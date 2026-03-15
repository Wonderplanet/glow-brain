using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record BossAppearancePauseModel(
        TickCount RemainingPauseFrames,
        IReadOnlyList<FieldObjectId> AppearedBossList)
    {
        public static BossAppearancePauseModel Empty { get; } = new BossAppearancePauseModel(
            TickCount.Empty,
            Array.Empty<FieldObjectId>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
