using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InGameGimmickObjectGenerationModelFactory : IInGameGimmickObjectGenerationModelFactory
    {
        public InGameGimmickObjectGenerationModel Create(MstAutoPlayerSequenceElementModel autoPlayerSequenceElementModel)
        {
            return new InGameGimmickObjectGenerationModel(
                autoPlayerSequenceElementModel.SequenceElementId,
                autoPlayerSequenceElementModel.SummonPosition,
                autoPlayerSequenceElementModel.Action.Value.ToMasterDataId());
        }
    }
}
