using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    // 強化クエストでの報酬倍率
    public record EnhanceQuestRewardAmountMultiplier(float Value)
    {
        public static EnhanceQuestRewardAmountMultiplier Empty { get; } = new (0);
        public static EnhanceQuestRewardAmountMultiplier Default { get; } = new (1);

        public override string ToString()
        {
            return Value.ToString();
        }

        public string GetMultiplierText()
        {
            return ZString.Format("報酬{0}倍!!", Value);
        }
    }
}
