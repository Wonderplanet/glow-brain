using System.Collections.Generic;
using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Core.Domain.Repositories
{
    public interface IReceivedDailyBonusRepository
    {
        void Load();
        void Save(IReadOnlyList<MissionReceivedDailyBonusModel> dailyBonusRewardModels);
        void Delete();
        IReadOnlyList<MissionReceivedDailyBonusModel> Get();
        bool IsExist();
    }
}