using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.Quest;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public record EventQuestSelectElementAssetPath(string Value)
    {
        const string AssetPath = "{0}_quest_select_cell";

        public static EventQuestSelectElementAssetPath Empty { get; } = new EventQuestSelectElementAssetPath(string.Empty);

        public static EventQuestSelectElementAssetPath FromAssetKey(QuestAssetKey assetKey)
        {

            return new EventQuestSelectElementAssetPath(ZString.Format(AssetPath, assetKey.Value));
        }
    };
}
