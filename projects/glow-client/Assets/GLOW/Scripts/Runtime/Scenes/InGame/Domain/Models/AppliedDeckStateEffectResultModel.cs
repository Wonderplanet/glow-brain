using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AppliedDeckStateEffectResultModel(
        BattleSide TargetDeckBattleSide,
        IReadOnlyList<FieldObjectId> AttackerIds,
        StateEffectType StateEffectType,
        PercentageM UpdatedParameter,
        MasterDataId TargetDeckCharacterId)
    {
        public static AppliedDeckStateEffectResultModel Empty { get; } = new (
            BattleSide.Player,
            new List<FieldObjectId>(),
            StateEffectType.None,
            PercentageM.Empty,
            MasterDataId.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
