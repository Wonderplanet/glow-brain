using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaThresholdText(ObscuredString Value)
    {
        public static GachaThresholdText Empty { get; } = new GachaThresholdText("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        // ガチャトップ用テキスト
        public static GachaThresholdText CreateGachaContentThresholdText(
            GachaThresholdText rarityThresholdText,
            int? rarityThreshold,
            GachaThresholdText pickupThresholdText,
            int? pickupThreshold)
        {
            if(!pickupThreshold.HasValue && !rarityThreshold.HasValue)
            {
                return Empty;
            }
            
            string text = "";
            if (pickupThreshold.HasValue)
            {

                text += ZString.Format("<color={0}>{1}回以内</color>に<color=#07ae7d>{2}</color>",
                    ColorCodeTheme.TextRed,
                    pickupThreshold,
                    pickupThresholdText.Value);
            }

            // テキストが入っていた場合改行を行う
            if (text != "")
            {
                text += "\n";
            }

            if (rarityThreshold.HasValue)
            {
                text += ZString.Format("<color={0}>{1}回以内</color>に<color=#07ae7d>{2}</color>",
                    ColorCodeTheme.TextRed,
                    rarityThreshold ,
                    rarityThresholdText.Value);
            }

            return new GachaThresholdText(text);
        }

        // ガチャ一覧用テキスト
        public static GachaThresholdText CreateGachaListThresholdText(
            GachaThresholdText rarityThresholdText,
            int? rarityThreshold,
            GachaThresholdText pickupThresholdText,
            int? pickupThreshold)
        {
            if(!pickupThreshold.HasValue && !rarityThreshold.HasValue)
            {
                return Empty;
            }
            
            string text = "";
            if (pickupThreshold.HasValue)
            {
                text += ZString.Format("<color={0}>{1}回以内</color>に{2}",
                    ColorCodeTheme.TextRed,
                    pickupThreshold,
                    pickupThresholdText.Value);
            }

            if (rarityThreshold.HasValue)
            {
                text += ZString.Format("<color={0}>{1}回以内</color>に{2}",
                    ColorCodeTheme.TextRed,
                    rarityThreshold ,
                    rarityThresholdText.Value);
            }

            return new GachaThresholdText(text);
        }
    }
}
