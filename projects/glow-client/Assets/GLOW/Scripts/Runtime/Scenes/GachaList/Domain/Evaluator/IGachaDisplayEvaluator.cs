using GLOW.Core.Domain.Models.OprData;

namespace GLOW.Scenes.GachaList.Domain.Evaluator
{
    public interface IGachaDisplayEvaluator
    {
        bool ShouldShowDisplay(OprGachaModel oprGachaModel);
    }
}
