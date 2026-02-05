using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary>
    /// 戦闘フィールド上の座標
    ///
    /// 原点：（プレイヤー拠点側の端, フィールド下端）
    /// X軸向き：プレイヤーの侵攻方向
    /// Y軸向き：フィールド上方向
    /// </summary>
    public record FieldCoordV2(ObscuredFloat X, ObscuredFloat Y)
    {
        public static FieldCoordV2 Empty = new FieldCoordV2(0f, 0f);
        public static FieldCoordV2 Zero = new FieldCoordV2(0f, 0f);

        public static FieldCoordV2 operator +(FieldCoordV2 a, FieldCoordV2 b)
        {
            return new FieldCoordV2(a.X + b.X, a.Y + b.Y);
        }

        public static FieldCoordV2 operator *(FieldCoordV2 a, float b)
        {
            return new FieldCoordV2(a.X * b, a.Y * b);
        }

        public static FieldCoordV2 operator *(float a, FieldCoordV2 b)
        {
            return new FieldCoordV2(a * b.X, a * b.Y);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
