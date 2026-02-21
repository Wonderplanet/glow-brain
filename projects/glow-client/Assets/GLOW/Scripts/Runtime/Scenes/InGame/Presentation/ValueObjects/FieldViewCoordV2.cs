namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    /// <summary>
    /// ワールド空間上の戦闘フィールド座標
    ///
    /// 原点：（プレイヤー拠点側の端, フィールド下端）
    /// X軸向き：プレイヤーの侵攻方向
    /// Y軸向き：フィールド上方向
    /// </summary>
    public record FieldViewCoordV2(float X, float Y)
    {
        public static readonly FieldViewCoordV2 Empty = new FieldViewCoordV2(0f, 0f);
        public static readonly FieldViewCoordV2 Zero = new FieldViewCoordV2(0f, 0f);
        
        public static FieldViewCoordV2 operator +(FieldViewCoordV2 a, FieldViewCoordV2 b)
        {
            return new FieldViewCoordV2(a.X + b.X, a.Y + b.Y);
        }

        public static FieldViewCoordV2 operator *(FieldViewCoordV2 a, float b)
        {
            return new FieldViewCoordV2(a.X * b, a.Y * b);
        }
        
        public static FieldViewCoordV2 operator *(float a, FieldViewCoordV2 b)
        {
            return new FieldViewCoordV2(a * b.X, a * b.Y);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}