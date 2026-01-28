using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface ISpecialAttackCutInLogRepository
    {
        void Load();
        void Save(SpecialAttackCutInLogModel selectedStageModel);
        SpecialAttackCutInLogModel Get();
    }
}