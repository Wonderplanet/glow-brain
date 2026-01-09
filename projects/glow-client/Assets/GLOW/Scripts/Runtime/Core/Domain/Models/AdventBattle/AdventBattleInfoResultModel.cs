namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleInfoResultModel(AdventBattleResultModel AdventBattleResult)
    {
        public static AdventBattleInfoResultModel Empty { get; } = new(AdventBattleResultModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}