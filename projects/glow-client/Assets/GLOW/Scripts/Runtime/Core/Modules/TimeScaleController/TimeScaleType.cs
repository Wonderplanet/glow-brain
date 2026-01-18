namespace GLOW.Core.Modules.TimeScaleController
{
    /// <summary>
    /// TimeScaleの適用方法を定義する列挙型
    /// </summary>
    public enum TimeScaleType
    {
        /// <summary>
        /// 乗算によるTimeScale適用
        /// 他のTimeScaleと乗算される
        /// </summary>
        Multiply,
        
        /// <summary>
        /// 固定値によるTimeScale適用
        /// 最も優先度の高いFixed値が基準となる
        /// </summary>
        Fixed
    }
}