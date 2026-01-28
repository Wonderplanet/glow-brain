namespace GLOW.Scenes.EventQuestSelect.Domain.ValueObject
{
    public record AdventBattleOpenStatus(AdventBattleOpenStatusType Value)
    {
        public static AdventBattleOpenStatus Empty { get; } = new(AdventBattleOpenStatusType.ClosedEmpty);
        public bool ButtonInteractable => Value
            is AdventBattleOpenStatusType.Opened
            or AdventBattleOpenStatusType.RankLocked;

        public bool GrayOutVisible => Value != AdventBattleOpenStatusType.Opened;

        public bool RemainTimeVisible => Value != AdventBattleOpenStatusType.RankLocked;

        public bool ButtonVisible => Value
            is AdventBattleOpenStatusType.Opened
            or AdventBattleOpenStatusType.BeforeOpened
            or AdventBattleOpenStatusType.RankLocked;
    };
}
