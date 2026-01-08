namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary>
    /// 拠点を基準にした座標系の座標
    ///
    /// プレイヤー拠点を基準にした座標系と、敵拠点を基準にした座標系の2種類ある
    ///
    /// 原点：拠点位置
    /// X軸向き：侵攻方向
    /// Y軸向き：フィールド上方向
    /// </summary>
    public record OutpostCoordV2(float X, float Y)
    {
        public static OutpostCoordV2 Empty { get; } = new(0f, 0f);

        public static OutpostCoordV2 operator +(OutpostCoordV2 a, OutpostCoordV2 b)
        {
            return new OutpostCoordV2(a.X + b.X, a.Y + b.Y);
        }

        public static OutpostCoordV2 Translate(OutpostCoordV2 vec, float x, float y)
        {
            return new OutpostCoordV2(vec.X + x, vec.Y + y);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
