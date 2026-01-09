using GLOW.Core.Modules.TimeScaleController;

namespace GLOW.Scenes.InGame.Presentation.Constants
{
    public static class TimeScalePriorityDefinitions
    {
        public static TimeScalePriority BattleSpeed { get; } = new (100);
        public static TimeScalePriority UnitDetail { get; } = new (200);
        public static TimeScalePriority MangaAnimation { get; } = new (300);
        public static TimeScalePriority Menu { get; } = new (400);
        public static TimeScalePriority BackGroundPause { get; } = new (500);
    }
}