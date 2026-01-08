using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Constants
{
    public static class InGameConstants
    {
        public static readonly TickCount BossAppearanceKnockBackFrames = new TickCount(60);

        public static readonly OutpostCoordV2 DefaultSummonPos = new OutpostCoordV2(0.1f, 0f);
        public static readonly Matrix3x3 FieldToPlayerOutpostMatrix = Matrix3x3.Translate(-0.15f, -0.05f);
        public static readonly TickCount HitStopDuration = new TickCount(10);
    }
}
