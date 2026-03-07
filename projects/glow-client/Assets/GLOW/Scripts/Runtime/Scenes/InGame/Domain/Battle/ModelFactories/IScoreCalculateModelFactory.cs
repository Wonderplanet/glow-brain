using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IScoreCalculateModelFactory
    {
        ScoreCalculateModel Create(
            InGameType type,
            QuestType questType,
            AdventBattleScoreAdditionModel scoreAdditionModel);
    }
}
