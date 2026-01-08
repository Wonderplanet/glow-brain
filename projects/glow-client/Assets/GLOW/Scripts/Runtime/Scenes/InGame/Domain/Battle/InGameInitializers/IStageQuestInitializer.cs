using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IStageQuestInitializer
    {
        StageQuestInitializationResult Initialize();
    }
}
