using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Core.Domain.Loader
{
    public class ReceivedDailyBonusRewardLoader : IReceivedDailyBonusRewardLoader
    {
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IReceivedDailyBonusRepository ReceivedDailyBonusRepository { get; }
        [Inject] IReceivedEventDailyBonusRepository ReceivedEventDailyBonusRepository { get; }
        [Inject] IReceivedComebackDailyBonusRepository ReceivedComebackDailyBonusRepository { get; }
        
        void IReceivedDailyBonusRewardLoader.LoadReceivedDailyBonusRewards()
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            if (!fetchOtherModel.MissionReceivedDailyBonusModel.IsEmpty())
            {
                // FetchOtherにデータが存在する場合、かつ端末保存されている情報がある場合はFetchOtherを優先する
                // そのため、端末保存されている情報を削除する
                if (ReceivedDailyBonusRepository.IsExist())
                {
                    ReceivedDailyBonusRepository.Delete();
                }

                return;
            }
            
            if (!ReceivedDailyBonusRepository.IsExist()) return;

            var rewards = ReceivedDailyBonusRepository.Get();
            var updatedFetchOtherModel = fetchOtherModel with
            {
                MissionReceivedDailyBonusModel = rewards
            };
            
            GameManagement.SaveGameFetchOther(updatedFetchOtherModel);
            
            ReceivedDailyBonusRepository.Delete();
        }

        void IReceivedDailyBonusRewardLoader.LoadReceivedEventDailyBonusRewards()
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            if (!fetchOtherModel.MissionEventDailyBonusRewardModels.IsEmpty())
            {
                // FetchOtherにデータが存在する場合、かつ端末保存されている情報がある場合はFetchOtherを優先する
                // そのため、端末保存されている情報を削除する
                if (ReceivedEventDailyBonusRepository.IsExist())
                {
                    ReceivedEventDailyBonusRepository.Delete();
                }

                return;
            }
            
            if (!ReceivedEventDailyBonusRepository.IsExist()) return;

            var rewards = ReceivedEventDailyBonusRepository.Get();
            
            var updatedFetchOtherModel = fetchOtherModel with
            {
                MissionEventDailyBonusRewardModels = rewards
            };
            
            GameManagement.SaveGameFetchOther(updatedFetchOtherModel);
            
            ReceivedEventDailyBonusRepository.Delete();
        }

        void IReceivedDailyBonusRewardLoader.LoadReceivedComebackDailyBonusRewards()
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            if (!fetchOtherModel.ComebackBonusRewardModels.IsEmpty())
            {
                // FetchOtherにデータが存在する場合、かつ端末保存されている情報がある場合はFetchOtherを優先する
                // そのため、端末保存されている情報を削除する
                if (ReceivedComebackDailyBonusRepository.IsExist())
                {
                    ReceivedComebackDailyBonusRepository.Delete();
                }

                return;
            }
            
            if (!ReceivedComebackDailyBonusRepository.IsExist()) return;

            var rewards = ReceivedComebackDailyBonusRepository.Get();
            
            var updatedFetchOtherModel = fetchOtherModel with
            {
                ComebackBonusRewardModels = rewards
            };
            
            GameManagement.SaveGameFetchOther(updatedFetchOtherModel);
            
            ReceivedComebackDailyBonusRepository.Delete();
        }
    }
}