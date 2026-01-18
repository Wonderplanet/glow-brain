using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;

namespace GLOW.Scenes.BoxGacha.Domain.Evaluator
{
    public interface IBoxGachaDrawableEvaluator
    {
        BoxGachaDrawableFlag Evaluate(MasterDataId mstEventId);
    }
}