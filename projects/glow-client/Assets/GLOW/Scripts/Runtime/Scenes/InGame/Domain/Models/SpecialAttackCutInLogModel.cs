using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpecialAttackCutInLogModel(
        ObscuredDateTimeOffset SpecialAttackOnceADayDate,
        IReadOnlyList<MasterDataId> PlayedSpecialAttackUnitIds)
    {
        public static SpecialAttackCutInLogModel Empty { get; } = new(
            DateTimeOffset.MinValue,
            new List<MasterDataId>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
