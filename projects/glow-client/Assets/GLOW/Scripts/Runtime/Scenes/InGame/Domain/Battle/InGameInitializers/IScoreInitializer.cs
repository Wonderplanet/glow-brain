using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IScoreInitializer
    {
        InGameScoreModel InitializeScore(QuestType questType);
    }
}
