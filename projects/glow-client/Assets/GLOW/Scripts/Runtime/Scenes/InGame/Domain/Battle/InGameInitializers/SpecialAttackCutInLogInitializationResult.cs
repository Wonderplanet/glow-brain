using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record SpecialAttackCutInLogInitializationResult(IReadOnlyList<MasterDataId> SpecialAttackCutInPlayedUnitIds)
    {
        public static SpecialAttackCutInLogInitializationResult Empty { get; } = new(
            new List<MasterDataId>()
        );
    }
}