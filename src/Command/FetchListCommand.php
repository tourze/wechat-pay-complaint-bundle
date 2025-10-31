<?php

namespace WechatPayComplaintBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatPayBundle\Entity\Merchant;
use WechatPayBundle\Repository\MerchantRepository;
use WechatPayBundle\Service\WechatPayBuilder;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;
use WechatPayComplaintBundle\Repository\ComplaintRepository;
use Yiisoft\Json\Json;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/list-complaints-v2.html
 */
#[AsCronTask(expression: '* * * * *')]
#[AsCommand(name: self::NAME, description: '查询投诉单列表')]
#[WithMonologChannel(channel: 'wechat_pay_complaint')]
class FetchListCommand extends Command
{
    public const NAME = 'wechat:pay:fetch-pay-complaint';

    public function __construct(
        public LoggerInterface $logger,
        public ComplaintRepository $complaintRepository,
        public ComplaintMediaRepository $mediaRepository,
        public MerchantRepository $merchantRepository,
        public WechatPayBuilder $wechatPayBuilder,
        public EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * @return array<string, mixed>
     */
    public function request(Merchant $merchant, int $limit, int $offset, string $startTime, string $endTime): array
    {
        $requestStartTime = microtime(true);
        $requestParams = $this->buildRequestParams($merchant, $limit, $offset, $startTime, $endTime);

        try {
            $this->logRequestStart($merchant, $requestParams);
            $responseData = $this->executeWechatRequest($merchant, $requestParams);
            $this->logRequestSuccess($merchant, $responseData, $requestStartTime);
            $this->processComplaintData($merchant, $responseData);

            return $responseData;
        } catch (\Exception $exception) {
            $this->logRequestFailure($merchant, $requestParams, $exception, $requestStartTime);
            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRequestParams(Merchant $merchant, int $limit, int $offset, string $startTime, string $endTime): array
    {
        return [
            'limit' => $limit,
            'offset' => $offset,
            'begin_date' => $startTime,
            'end_date' => $endTime,
            'complainted_mchid' => $merchant->getMchId(),
        ];
    }

    /**
     * @param array<string, mixed> $requestParams
     */
    private function logRequestStart(Merchant $merchant, array $requestParams): void
    {
        $query = http_build_query($requestParams);
        $this->logger->info('微信支付投诉单列表请求开始', [
            'merchant_id' => $merchant->getMchId(),
            'request_params' => $requestParams,
            'request_url' => "/v3/merchant-service/complaints-v2?{$query}",
        ]);
    }

    /**
     * @param array<string, mixed> $requestParams
     * @return array<string, mixed>
     */
    private function executeWechatRequest(Merchant $merchant, array $requestParams): array
    {
        $builder = $this->wechatPayBuilder->genBuilder($merchant);
        $query = http_build_query($requestParams);
        $response = $builder->chain("/v3/merchant-service/complaints-v2?{$query}")->get();
        $responseContent = $response->getBody()->getContents();
        $responseData = Json::decode($responseContent);

        if (!is_array($responseData)) {
            throw new \InvalidArgumentException('Invalid response format from WeChat Pay API');
        }

        // Ensure the array has string keys
        /** @var array<string, mixed> $validatedData */
        $validatedData = [];
        foreach ($responseData as $key => $value) {
            if (is_string($key)) {
                $validatedData[$key] = $value;
            }
        }

        return $validatedData;
    }

    /**
     * @param array<string, mixed> $responseData
     */
    private function logRequestSuccess(Merchant $merchant, array $responseData, float $requestStartTime): void
    {
        $requestEndTime = microtime(true);
        $requestDuration = ($requestEndTime - $requestStartTime) * 1000;

        $this->logger->info('微信支付投诉单列表请求成功', [
            'merchant_id' => $merchant->getMchId(),
            'response_data' => $responseData,
            'duration_ms' => round($requestDuration, 2),
            'total_count' => is_int($responseData['total_count'] ?? null) ? $responseData['total_count'] : 0,
            'data_count' => count(is_array($responseData['data'] ?? null) ? $responseData['data'] : []),
        ]);
    }

    /**
     * @param array<string, mixed> $responseData
     */
    private function processComplaintData(Merchant $merchant, array $responseData): void
    {
        $data = $responseData['data'] ?? [];
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid data format in response');
        }

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            // Ensure the item array has string keys
            /** @var array<string, mixed> $validatedItem */
            $validatedItem = [];
            foreach ($item as $key => $value) {
                if (is_string($key)) {
                    $validatedItem[$key] = $value;
                }
            }

            $this->createComplaintFromData($merchant, $validatedItem);
        }
    }

    /**
     * @param array<string, mixed> $item
     */
    private function createComplaintFromData(Merchant $merchant, array $item): void
    {
        $complaint = new Complaint();
        $complaint->setMerchant($merchant);
        $complaint->setWxComplaintId(is_string($item['complaint_id'] ?? null) ? $item['complaint_id'] : '');
        $complaint->setComplaintTime(is_string($item['complaint_time'] ?? null) ? $item['complaint_time'] : '');
        $complaint->setComplaintDetail(is_string($item['complaint_detail'] ?? null) ? $item['complaint_detail'] : null);
        $complaintState = $item['complaint_state'] ?? '';
        $complaint->setComplaintState(ComplaintState::tryFrom(is_string($complaintState) || is_int($complaintState) ? $complaintState : ''));
        $complaint->setPayerPhone(is_string($item['payer_phone'] ?? null) ? $item['payer_phone'] : null);

        $this->setOrderInfoFromData($complaint, $item);
        $this->setAdditionalFieldsFromData($complaint, $item);

        $this->entityManager->persist($complaint);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $item
     */
    private function setOrderInfoFromData(Complaint $complaint, array $item): void
    {
        if (!isset($item['complaint_order_info']) || !is_array($item['complaint_order_info']) || [] === $item['complaint_order_info']) {
            return;
        }

        $firstOrder = $item['complaint_order_info'][0];
        if (!is_array($firstOrder)) {
            return;
        }

        $complaint->setWxPayOrderNo(is_string($firstOrder['transaction_id'] ?? null) ? $firstOrder['transaction_id'] : null);
        $complaint->setPayOrderNo(is_string($firstOrder['out_trade_no'] ?? null) ? $firstOrder['out_trade_no'] : '');
        $amount = $firstOrder['amount'] ?? 0.0;
        $complaint->setAmount(is_numeric($amount) ? (float) $amount : 0.0);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function setAdditionalFieldsFromData(Complaint $complaint, array $item): void
    {
        $complaint->setComplaintFullRefunded(is_bool($item['complaint_full_refunded'] ?? null) ? $item['complaint_full_refunded'] : false);
        $complaint->setUserComplaintTimes(is_int($item['user_complaint_times'] ?? null) ? $item['user_complaint_times'] : 0);
        $complaint->setProblemDescription(is_string($item['problem_description'] ?? null) ? $item['problem_description'] : null);
        $refundAmount = $item['apply_refund_amount'] ?? null;
        $complaint->setApplyRefundAmount(is_numeric($refundAmount) ? (float) $refundAmount : null);
    }

    /**
     * @param array<string, mixed> $requestParams
     */
    private function logRequestFailure(Merchant $merchant, array $requestParams, \Exception $exception, float $requestStartTime): void
    {
        $requestEndTime = microtime(true);
        $requestDuration = ($requestEndTime - $requestStartTime) * 1000;

        $this->logger->error('微信支付投诉单列表请求失败', [
            'merchant_id' => $merchant->getMchId(),
            'request_params' => $requestParams,
            'duration_ms' => round($requestDuration, 2),
            'exception_message' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'exception_code' => $exception->getCode(),
            'exception_trace' => $exception->getTraceAsString(),
        ]);
    }

    protected function configure(): void
    {
        $this->setDescription('查询投诉单列表')
            ->addArgument('startTime', InputArgument::OPTIONAL, 'start time', (new \DateTime('today'))->format('Y-m-d'))
            ->addArgument('endTime', InputArgument::OPTIONAL, 'end time', (new \DateTime('today 23:59:59'))->format('Y-m-d'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = $input->getArgument('startTime');
        $endTime = $input->getArgument('endTime');

        if (!is_string($startTime) || !is_string($endTime)) {
            throw new \InvalidArgumentException('Start time and end time must be strings');
        }

        $merchants = $this->merchantRepository->findAll();
        foreach ($merchants as $merchant) {
            $limit = 20;
            $offset = 0;

            do {
                $responseData = $this->request($merchant, $limit, $offset, $startTime, $endTime);
                $totalCount = is_int($responseData['total_count'] ?? null) ? $responseData['total_count'] : 0;
                $offset += $limit;
            } while ($offset < $totalCount);
        }

        return Command::SUCCESS;
    }
}
