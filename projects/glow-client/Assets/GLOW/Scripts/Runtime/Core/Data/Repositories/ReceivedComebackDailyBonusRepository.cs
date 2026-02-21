using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.DataStores.Mission;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.ComebackDailyBonus;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using Zenject;

namespace GLOW.Core.Data.Repositories
{
    public class ReceivedComebackDailyBonusRepository : IReceivedComebackDailyBonusRepository
    {
        [Inject] IReceivedComebackDailyBonusDataStore ReceivedComebackDailyBonusDataStore { get; }
        
        void IReceivedComebackDailyBonusRepository.Load()
        {
            ReceivedComebackDailyBonusDataStore.Load();
        }

        void IReceivedComebackDailyBonusRepository.Save(IReadOnlyList<ComebackBonusRewardModel> comebackDailyBonusRewardModels)
        {
            var rewards = comebackDailyBonusRewardModels
                .Select(ComebackDailyBonusRewardModelTranslator.ToComebackDailyBonusRewardData)
                .ToArray();
            
            ReceivedComebackDailyBonusDataStore.Save(rewards);
        }

        void IReceivedComebackDailyBonusRepository.Delete()
        {
            ReceivedComebackDailyBonusDataStore.Delete();
        }

        IReadOnlyList<ComebackBonusRewardModel> IReceivedComebackDailyBonusRepository.Get()
        {
            var rewards = ReceivedComebackDailyBonusDataStore.Get();
            var comebackDailyBonus = rewards
                .Select(data => new ComebackBonusRewardModel(
                    new MasterDataId(data.MstComebackBonusScheduleId),
                    new LoginDayCount(data.LoginDayCount),
                    RewardDataTranslator.Translate(data.Reward)))
                .ToList();
            
            return comebackDailyBonus;
        }

        bool IReceivedComebackDailyBonusRepository.IsExist()
        {
            return ReceivedComebackDailyBonusDataStore.IsExist();
        }
    }
}