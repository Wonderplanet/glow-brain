using System.Collections.Generic;
using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Core.Domain.Repositories
{
    public interface IReceivedEventDailyBonusRepository
    {
        void Load();
        void Save(IReadOnlyList<MissionEventDailyBonusRewardModel> eventDailyBonusRewardModels);
        void Delete();
        IReadOnlyList<MissionEventDailyBonusRewardModel> Get();
        bool IsExist();
    }
}