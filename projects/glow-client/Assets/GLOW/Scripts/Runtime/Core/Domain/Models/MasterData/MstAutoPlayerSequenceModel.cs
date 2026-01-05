using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstAutoPlayerSequenceModel(
        AutoPlayerSequenceSetId SequenceSetId,
        IReadOnlyList<MstAutoPlayerSequenceElementModel> Elements)
    {
        public static MstAutoPlayerSequenceModel Empty { get; } = new(
            AutoPlayerSequenceSetId.Empty,
            new List<MstAutoPlayerSequenceElementModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public IEnumerable<MstAutoPlayerSequenceElementModel> EnemySummonElements => Elements
            .Where(element => element.Action.Type == AutoPlayerSequenceActionType.SummonEnemy);

        public IEnumerable<MstAutoPlayerSequenceElementModel> GimmickObjectSummonElements => Elements
            .Where(element => element.Action.Type == AutoPlayerSequenceActionType.SummonGimmickObject);

        public IEnumerable<MstAutoPlayerSequenceElementModel> TransformGimmickObjectToEnemyElements => Elements
            .Where(element => element.Action.Type == AutoPlayerSequenceActionType.TransformGimmickObjectToEnemy);
    }
}
