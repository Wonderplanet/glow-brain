using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class OutpostEnhancementTypeExtensions
    {
        public static string ToDisplayString(this OutpostEnhancementType type)
        {
            switch (type)
            {
                case OutpostEnhancementType.LeaderPointSpeed:
                    return "リーダーPチャージ量";
                case OutpostEnhancementType.LeaderPointLimit:
                    return "リーダーP最大値";
                case OutpostEnhancementType.OutpostHP:
                    return "ゲートHP";
                case OutpostEnhancementType.SummonInterval:
                    return "召喚クールタイム短縮";
                case OutpostEnhancementType.LeaderPointUp:
                    return "リーダーP初期値";
                case OutpostEnhancementType.RushChargeSpeed:
                    return "総攻撃チャージ短縮";
                default:
                    return type.ToString();
            }
        }
    }
}

