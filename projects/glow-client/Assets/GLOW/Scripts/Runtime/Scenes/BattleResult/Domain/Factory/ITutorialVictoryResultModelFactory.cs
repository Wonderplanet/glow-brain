using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface ITutorialVictoryResultModelFactory
    {
        VictoryResultModel CreateTutorialVictoryResultModel(
            TutorialStageEndResultModel tutorialStageEndResultModel,
            UserParameterModel prevUserParameterModel,
            MasterDataId mstStageId);
    }
}
