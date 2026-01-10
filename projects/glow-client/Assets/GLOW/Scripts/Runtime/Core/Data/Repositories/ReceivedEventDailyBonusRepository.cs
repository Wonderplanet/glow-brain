using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.DataStores.Mission;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using Zenject;

namespace GLOW.Core.Data.Repositories
{
    public class ReceivedEventDailyBonusRepository : IReceivedEventDailyBonusRepository
    {
        [Inject] IReceivedEventDailyBonusDataStore ReceivedEventDailyBonusDataStore { get; }
        
        void IReceivedEventDailyBonusRepository.Load()
        {
            ReceivedEventDailyBonusDataStore.Load();
        }

        void IReceivedEventDailyBonusRepository.Save(IReadOnlyList<MissionEventDailyBonusRewardModel> eventDailyBonusRewardModels)
        {
            var rewards = eventDailyBonusRewardModels
                .Select(EventDailyBonusRewardModelTranslator.ToEventDailyBonusRewardData)
                .ToArray();
            
            ReceivedEventDailyBonusDataStore.Save(rewards);
        }

        void IReceivedEventDailyBonusRepository.Delete()
        {
            ReceivedEventDailyBonusDataStore.Delete();
        }

        IReadOnlyList<MissionEventDailyBonusRewardModel> IReceivedEventDailyBonusRepository.Get()
        {
            var rewards = ReceivedEventDailyBonusDataStore.Get();
            var eventDailyBonus = rewards
                .Select(data => new MissionEventDailyBonusRewardModel(
                    new MasterDataId(data.MstMissionEventDailyBonusScheduleId),
                    new LoginDayCount(data.LoginDayCount),
                    RewardDataTranslator.Translate(data.Reward)))
                .ToList();
            
            return eventDailyBonus;
        }

        bool IReceivedEventDailyBonusRepository.IsExist()
        {
            return ReceivedEventDailyBonusDataStore.IsExist();
        }
    }
}