using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Loader;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.ComebackDailyBonus;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.ComeBackDailyBonus.Domain.Model;
using Zenject;

namespace GLOW.Scenes.ComeBackDailyBonus.Domain.UseCase
{
    public class ShowComebackDailyBonusUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstComebackBonusDataRepository MstComebackBonusDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IReceivedDailyBonusRewardLoader ReceivedDailyBonusRewardLoader { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        public ComebackDailyBonusModel UpdateAndFetchComebackDailyBonusModel(MasterDataId mstComebackDailyBonusScheduleId)
        {
            // 受け取り済み報酬の情報をロードする
            ReceivedDailyBonusRewardLoader.LoadReceivedComebackDailyBonusRewards();
            
            var gameFetchOther = GameRepository.GetGameFetchOther();
            
            var userComebackDailyBonusModels = gameFetchOther.UserComebackBonusProgressModels;
            var userComebackDailyBonusModel = userComebackDailyBonusModels
                .FirstOrDefault(model => model.MstComebackBonusScheduleId == mstComebackDailyBonusScheduleId,
                    UserComebackBonusProgressModel.Empty);

            var receivedRewards = gameFetchOther.ComebackBonusRewardModels;
            var commonReceiveModels = receivedRewards
                .Where(model => model.MstComebackBonusScheduleId == mstComebackDailyBonusScheduleId)
                .Select(model => CreateCommonReceiveModel(model.Reward))
                .ToList();
            
            // カムバックボーナスは1日につき1日分だけ受け取れる
            var receivedLoginDayCount = receivedRewards
                .FirstOrDefault(
                    reward => reward.MstComebackBonusScheduleId == mstComebackDailyBonusScheduleId, 
                    ComebackBonusRewardModel.Empty)
                .LoginDayCount;
            
            var mstComebackDailyBonusModels = MstComebackBonusDataRepository.GetMstComebackBonusModels(mstComebackDailyBonusScheduleId);
            if (mstComebackDailyBonusModels.IsEmpty()) return ComebackDailyBonusModel.Empty;
            
            var cellModels = mstComebackDailyBonusModels
                .Select(model => CreateComebackDailyBonusCellModel(model, receivedLoginDayCount, userComebackDailyBonusModel.ProgressLoginDayCount))
                .ToList();
            
            // 残り時間を計算する(空だった場合、または終了時間を過ぎていた場合は空にする)
            var remainingTime = userComebackDailyBonusModel.EndAt.IsEmpty() || userComebackDailyBonusModel.EndAt < TimeProvider.Now ?
                RemainingTimeSpan.Empty :
                userComebackDailyBonusModel.EndAt - TimeProvider.Now;

            // 受け取り済み報酬をクリアする（受け取り済み報酬は一度表示したら消す仕様のため）
            var updatedFetchOther = gameFetchOther with
            {
                ComebackBonusRewardModels = new List<ComebackBonusRewardModel>()
            };
            GameManagement.SaveGameFetchOther(updatedFetchOther);

            return new ComebackDailyBonusModel(
                userComebackDailyBonusModel.ProgressLoginDayCount,
                cellModels,
                commonReceiveModels,
                remainingTime);
        }
        
        ComebackDailyBonusCellModel CreateComebackDailyBonusCellModel(
            MstComebackBonusModel mstComebackBonusModel,
            LoginDayCount receiveRewardLoginDayCount,
            LoginDayCount progressLoginDayCount)
        {
            var reward = MstComebackBonusDataRepository.GetMstDailyBonusRewardModelFirstOrDefault(
                mstComebackBonusModel.MstMissionRewardGroupId);
            
            var receiveStatus = DailyBonusReceiveStatus.CannotReceive;
            
            // 進行中のログイン日数以下のものは受け取り済みか受け取り予定
            if (mstComebackBonusModel.LoginDayCount <= progressLoginDayCount)
            {
                if (!receiveRewardLoginDayCount.IsEmpty() && receiveRewardLoginDayCount == mstComebackBonusModel.LoginDayCount)
                {
                    // 受け取った報酬でログイン日数が一致する場合は演出させる
                    receiveStatus = DailyBonusReceiveStatus.Receiving;
                }
                else
                {
                    // 受け取った報酬でログイン日数が一致しない場合は受け取り済み
                    receiveStatus = DailyBonusReceiveStatus.Received;
                }
            }
            
            return new ComebackDailyBonusCellModel(
                receiveStatus,
                mstComebackBonusModel.LoginDayCount,
                PlayerResourceModelFactory.Create(
                    reward.ResourceType,
                    reward.ResourceId,
                    reward.ResourceAmount.ToPlayerResourceAmount()),
                reward.SortOrder);
        }
        
        CommonReceiveResourceModel CreateCommonReceiveModel(RewardModel reward)
        {
            return new CommonReceiveResourceModel(
                reward.UnreceivedRewardReasonType,
                PlayerResourceModelFactory.Create(
                    reward.ResourceType,
                    reward.ResourceId,
                    reward.Amount),
                PlayerResourceModelFactory.Create(reward.PreConversionResource));
        }
    }
}