namespace GLOW.Core.Modules.TimeScaleController
{
    public interface ITimeScaleController
    {
        /// <summary>
        /// TimeScaleを変更する（タイプと優先度を指定）
        /// </summary>
        /// <param name="timeScale">設定するTimeScale値</param>
        /// <param name="type">適用方法（Multiply or Fixed）</param>
        /// <param name="priority">優先度（高い値ほど優先）</param>
        ITimeScaleControlHandler ChangeTimeScale(float timeScale, TimeScaleType type, TimeScalePriority priority);
    }
}