using System.Collections.Generic;
using GLOW.Core.Domain.Models.ComebackDailyBonus;

namespace GLOW.Core.Domain.Repositories
{
    public interface IReceivedComebackDailyBonusRepository
    {
        void Load();
        void Save(IReadOnlyList<ComebackBonusRewardModel> comebackDailyBonusRewardModels);
        void Delete();
        IReadOnlyList<ComebackBonusRewardModel> Get();
        bool IsExist();
    }
}