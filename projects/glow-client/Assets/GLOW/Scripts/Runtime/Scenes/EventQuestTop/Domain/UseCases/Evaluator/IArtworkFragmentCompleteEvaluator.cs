using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public interface IArtworkFragmentCompleteEvaluator
    {
        StageRewardCompleteFlag Evaluate(MasterDataId mstArtworkFragmentDropGroupId);
    }
}