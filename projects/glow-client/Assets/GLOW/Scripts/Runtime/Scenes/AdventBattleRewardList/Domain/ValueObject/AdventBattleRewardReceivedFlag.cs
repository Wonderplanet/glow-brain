namespace GLOW.Scenes.AdventBattleRewardList.Domain.ValueObject
{
    public record AdventBattleRewardReceivedFlag(bool Value)
    {
        public static AdventBattleRewardReceivedFlag True { get; } = new AdventBattleRewardReceivedFlag(true);
        public static AdventBattleRewardReceivedFlag False { get; } = new AdventBattleRewardReceivedFlag(false);
        
        public static implicit operator bool(AdventBattleRewardReceivedFlag flag) => flag.Value;
    }
}