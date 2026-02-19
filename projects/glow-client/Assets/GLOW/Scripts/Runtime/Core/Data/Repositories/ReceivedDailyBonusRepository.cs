using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.DataStores.Mission;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Mission;
using Zenject;

namespace GLOW.Core.Data.Repositories
{
    public class ReceivedDailyBonusRepository : IReceivedDailyBonusRepository
    {
        [Inject] IReceivedDailyBonusDataStore ReceivedDailyBonusDataStore { get; }
        
        void IReceivedDailyBonusRepository.Load()
        {
            ReceivedDailyBonusDataStore.Load();
        }

        void IReceivedDailyBonusRepository.Save(IReadOnlyList<MissionReceivedDailyBonusModel> dailyBonusRewardModels)
        {
            var rewards = dailyBonusRewardModels
                .Select(DailyBonusRewardModelTranslator.ToDailyBonusRewardData)
                .ToArray();
            
            ReceivedDailyBonusDataStore.Save(rewards);
        }

        void IReceivedDailyBonusRepository.Delete()
        {
            ReceivedDailyBonusDataStore.Delete();
        }

        IReadOnlyList<MissionReceivedDailyBonusModel> IReceivedDailyBonusRepository.Get()
        {
            var rewards = ReceivedDailyBonusDataStore.Get();
            var dailyBonus = rewards
                .Select(data => new MissionReceivedDailyBonusModel(
                    data.MissionType,
                    new LoginDayCount(data.LoginDayCount),
                    RewardDataTranslator.Translate(data.Reward)))
                .ToList();
            
            return dailyBonus;
        }

        bool IReceivedDailyBonusRepository.IsExist()
        {
            return ReceivedDailyBonusDataStore.IsExist();
        }
    }
}