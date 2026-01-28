using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Campaign;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Components;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using UIKit;
using UnityEngine;
using UnityEngine.Serialization;
using UnityEngine.UI;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class HomeMainQuestView : UIView
    {
        [Header("ステージ選択カルーセル")]
        [SerializeField] GlowCustomInfiniteCarouselView _carouselView;
        [SerializeField] float _maxDistanceMargin = 2.5f;
        [SerializeField] float _cellSizeMargin = 0.3f;

        [Header("クエスト情報")]
        [SerializeField] HomeMainQuestSymbolImage _questImage;
        [SerializeField] UIText _questName;
        [SerializeField] GameObject _limitTimeObject;
        [SerializeField] UIText _limitTimeText;

        [Header("クエスト難易度選択表示")]
        [SerializeField] QuestDifficultyLabelComponent _difficultyLabelComponent;

        [Header("ステージ選択ボタン")]
        [SerializeField] Button _leftButton;
        [SerializeField] Button _rightButton;

        [Header("推奨レベル")]
        [SerializeField] GameObject _recommendedLevelAreaObj;
        [SerializeField] UIText _recommendedLevel;

        [Header("キャンペーン")]
        [SerializeField] CampaignBalloonMultiSwitcherComponent _campaignBalloonMultiSwitcherComponent;

        public GlowCustomInfiniteCarouselView CarouselView => _carouselView;
        public float MaxDistanceMargin => _maxDistanceMargin;
        public float CellSizeMargin => _cellSizeMargin;

        public HomeMainQuestSymbolImage QuestImage => _questImage;
        public UIText QuestName => _questName;
        public QuestDifficultyLabelComponent DifficultyLabelComponent => _difficultyLabelComponent;
        public Button LeftButton => _leftButton;
        public Button RightButton => _rightButton;

        public void SetupQuestTimeLimit(QuestLimitTime limitTime)
        {
            _limitTimeObject.SetActive(!limitTime.IsEmpty);
            _limitTimeText.SetText(TimeSpanFormatter.Format(limitTime.Value));
        }

        public void SetRecommendedLevel(int level, bool isVisible)
        {
            _recommendedLevelAreaObj.SetActive(isVisible);
            if(isVisible)
            {
                var format = "<size=16>推奨キャラ</size><size=18>Lv.{0}</size>";
                _recommendedLevel.SetText(format, level);
            }
        }

        public void SetUpCampaignBalloons(IReadOnlyList<CampaignViewModel> campaignViewModels)
        {
            _campaignBalloonMultiSwitcherComponent.SetUpCampaignBalloons(campaignViewModels);
        }
    }
}
