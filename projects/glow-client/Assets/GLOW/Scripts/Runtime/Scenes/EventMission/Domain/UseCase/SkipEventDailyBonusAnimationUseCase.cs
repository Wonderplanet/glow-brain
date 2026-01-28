using System.Collections.Generic;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public class SkipEventDailyBonusAnimationUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IReceivedEventDailyBonusRepository ReceivedEventDailyBonusRepository { get; }
        
        public void ClearEventDailyBonusRewardModels()
        {
            var fetchOther = GameRepository.GetGameFetchOther();
            if (fetchOther.MissionEventDailyBonusRewardModels.IsEmpty()) return;
            
            var updatedFetchOther = fetchOther with
            {
                // 受け取り演出時は含まれている状態、演出後リセット
                MissionEventDailyBonusRewardModels = new List<MissionEventDailyBonusRewardModel>()
            };
            GameManagement.SaveGameFetchOther(updatedFetchOther);

            if (ReceivedEventDailyBonusRepository.IsExist())
            {
                // 端末保存されている情報がある場合は削除
                ReceivedEventDailyBonusRepository.Delete();
            }
        }
    }
}