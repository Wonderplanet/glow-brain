using GLOW.Core.Presentation.Components;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public class QuestContentSectionHeader : UICollectionViewSectionHeader
    {
        [SerializeField] UIText _bannerText;

        // 切り替え表示処理
        public void SetContentSection(QuestContentTopSectionType sectionType)
        {
            var text = sectionType switch
            {
                QuestContentTopSectionType.Daily => "デイリーイベント",
                QuestContentTopSectionType.EndContent => "ランキングイベント",
                QuestContentTopSectionType.Event => "期間限定イベント",
                QuestContentTopSectionType.Pvp => "VSプレイヤー",
            };
            _bannerText.SetText(text);
        }
    }
}
