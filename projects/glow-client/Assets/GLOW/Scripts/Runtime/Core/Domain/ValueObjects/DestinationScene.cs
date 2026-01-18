using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public enum DestinationSceneEnum
    {
        Empty,
        QuestSelect,
        StageSelect,
        Home,
        IdleIncentive,
        Web,
        UnitList,
        OutpostEnhance,
        Shop,
        Pack,
        Pass,
        Event,
        Gacha,
        Pvp,
        LinkBnId,
        Exchange,
    }

    public record DestinationScene(ObscuredString Value)
    {
        public static DestinationScene Empty { get; } = new DestinationScene("");
        public static DestinationScene QuestSelect { get; } = new DestinationScene("QuestSelect");
        public static DestinationScene StageSelect { get; } = new DestinationScene("StageSelect");
        public static DestinationScene Home { get; } = new DestinationScene("Home");
        public static DestinationScene IdleIncentive { get; } = new DestinationScene("IdleIncentive");
        public static DestinationScene Web { get; } = new DestinationScene("Web");
        public static DestinationScene UnitList { get; } = new DestinationScene("UnitList");
        public static DestinationScene OutpostEnhance { get; } = new DestinationScene("OutPostEnhance");
        public static DestinationScene Shop { get; } = new DestinationScene("Shop");
        public static DestinationScene Pack { get; } = new DestinationScene("Pack");
        public static DestinationScene Pass { get; } = new DestinationScene("Pass");
        public static DestinationScene Event { get; } = new DestinationScene("Event");
        public static DestinationScene Gacha { get; } = new DestinationScene("Gacha");
        public static DestinationScene Pvp { get; } = new DestinationScene("Pvp");
        public static DestinationScene LinkBnId { get; } = new DestinationScene("LinkBnId");
        public static DestinationScene Exchange { get; } = new DestinationScene("Exchange");

        public bool IsQuestSelect()
        {
            return Value == QuestSelect.Value;
        }

        public bool IsStageSelect()
        {
            return Value == StageSelect.Value;
        }

        public bool IsHome()
        {
            return Value == Home.Value;
        }

        public bool IsIdleIncentive()
        {
            return Value == IdleIncentive.Value;
        }

        public bool IsWeb()
        {
            return Value == Web.Value;
        }

        public bool IsUnitList()
        {
            return Value == UnitList.Value;
        }

        public bool IsOutpostEnhance()
        {
            return Value == OutpostEnhance.Value;
        }

        public bool IsShop()
        {
            return Value == Shop.Value;
        }

        public bool IsPack()
        {
            return Value == Pack.Value;
        }

        public bool IsPass()
        {
            return Value == Pass.Value;
        }

        public bool IsEventTop()
        {
            return Value == Event.Value;
        }

        public bool IsGacha()
        {
            return Value == Gacha.Value;
        }

        public bool IsPvp()
        {
            return Value == Pvp.Value;
        }

        public bool IsLinkBnId()
        {
            return Value == LinkBnId.Value;
        }

        public bool IsExchange()
        {
            return Value == Exchange.Value;
        }

        public DestinationSceneEnum TryToEnum()
        {
            var isParseSuccess = Enum.TryParse(Value, out DestinationSceneEnum returnEnum);
            if (!isParseSuccess)
            {
                return DestinationSceneEnum.Empty;
            }

            return returnEnum;
        }
    }
}
