using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public static class InterruptAnimationPriorityDefinitions
    {
        public static InterruptAnimationPriority MangaAnimation { get; } = new(10);
        public static InterruptAnimationPriority BossAppearance { get; } = new(20);
        public static InterruptAnimationPriority SpecialUnitSummon { get; } = new(25);
        public static InterruptAnimationPriority SpecialAttackCutIn { get; } = new(30);
        public static InterruptAnimationPriority UnitTransformation { get; } = new(40);
    }
}