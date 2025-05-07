<?php

namespace WechatPayComplaintBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatPayBundle\Entity\Merchant;
use WechatPayBundle\Enum\ComplaintState;
use WechatPayBundle\Repository\MerchantRepository;
use WechatPayBundle\Service\WechatPayBuilder;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;
use WechatPayComplaintBundle\Repository\ComplaintRepository;
use Yiisoft\Json\Json;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/list-complaints-v2.html
 */
#[AsCronTask('* * * * *')]
#[AsCommand(name: 'wechat:pay:fetch-pay-complaint', description: '查询投诉单列表')]
class FetchListCommand extends Command
{
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

    public function request(Merchant $merchant, int $limit, int $offset, string $startTime, string $endTime): void
    {
        $builder = $this->wechatPayBuilder->genBuilder($merchant);
        $query = http_build_query([
            'limit' => $limit,
            'offset' => $offset,
            'begin_date' => $startTime,
            'end_date' => $endTime,
            'complainted_mchid' => $merchant->getMchId(),
        ]);
        $response = $builder->chain("/v3/merchant-service/complaints-v2?{$query}")->get();
        $response = $response->getBody()->getContents();
        $response = Json::decode($response);
        $this->logger->info('查询投诉单列表', $response);

        foreach ($response['data'] as $item) {
            $complaint = new Complaint();
            $complaint->setMerchant($merchant);
            $complaint->setWxComplaintId($item['complaint_id']);
            $complaint->setComplaintTime($item['complaint_time']);
            $complaint->setComplaintDetail($item['complaint_detail']);
            $complaint->setComplaintState(ComplaintState::tryFrom($item['complaint_state']));
            $complaint->setPayerPhone($item['payer_phone']);
            foreach ($item['complaint_order_info'] as $order) {
                $complaint->setWxPayOrderNo($order['transaction_id']);
                $complaint->setPayOrderNo($order['out_trade_no']);
                $complaint->setAmount($order['amount']);
            }
            $complaint->setComplaintFullRefunded($item['complaint_full_refunded']);
            $complaint->setUserComplaintTimes($item['user_complaint_times']);
            $complaint->setProblemDescription($item['problem_description']);
            $complaint->setApplyRefundAmount($item['apply_refund_amount']);
            $this->entityManager->persist($complaint);
            $this->entityManager->flush();
        }

        if (($offset + 1) * $limit < $response['total_count']) {
            $this->request($merchant, $limit, $offset + $limit, $startTime, $endTime);
        }
    }

    protected function configure(): void
    {
        $this->setDescription('查询投诉单列表')
            ->addArgument('startTime', InputArgument::OPTIONAL, 'start time', Carbon::now()->startOfDay()->getTimestamp())
            ->addArgument('endTime', InputArgument::OPTIONAL, 'end time', Carbon::now()->endOfDay()->getTimestamp());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = $input->getArgument('startTime');
        $endTime = $input->getArgument('endTime');

        $merchants = $this->merchantRepository->findAll();
        foreach ($merchants as $merchant) {
            $this->request($merchant, 20, 0, $startTime, $endTime);
        }

        return Command::SUCCESS;
    }
}
