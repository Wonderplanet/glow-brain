using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Scenes.EventQuestTop.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EventQuestTop.Presentation.Components
{
    public class EventQuestTopStageCell : GlowCustomInfiniteCarouselCell
    {
        [Header("Cell本体")]
        [SerializeField] Button _rootButton;
        [Header("未開放")]
        [SerializeField] GameObject _unreleaseGameObject;
        [SerializeField] Button _unreleaseButton;
        [SerializeField] GameObject _unreleaseLockIcon;
        [SerializeField] UIText _unreleaseText;
        [Header("開放済み")]
        [SerializeField] GameObject _releasedGameObject;
        [SerializeField] UIText _releasedStageNumber;
        [SerializeField] Button _stageInfoButton;

        [Header("開放済み/ステージアイコン")]
        [SerializeField] Image _stageIconImage;
        [Header("開放済み/クリア状況")]
        [SerializeField] GameObject _newIconObject;
        [SerializeField] GameObject _clearIconObject;
        [SerializeField] GameObject _dailyIconObject;
        [Header("開放済み/原画のかけらアイコン")]
        [SerializeField] Image _artworkFragmentIconImage;
        [Header("解放済み/報酬宝箱アイコン")]
        [SerializeField] GameObject _rewardIconObject;


        public GameObject ReleasedGameObject => _releasedGameObject;

        protected override void Awake()
        {
            base.Awake();
            AddButton(_stageInfoButton, "info");
            AddButton(_unreleaseButton, "unRelease");
        }

        public void OnUpdateButtonStatus(int centerIndex)
        {
            _rootButton.enabled = Index != centerIndex;
            _stageInfoButton.enabled =Index == centerIndex;
            _unreleaseButton.enabled = Index == centerIndex;
        }

        public void SetUpCell(EventQuestTopElementViewModel viewModel)
        {
            _releasedStageNumber.SetText(viewModel.StageNumber.Value.ToString());
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_stageIconImage, viewModel.StageIconAssetPath.Value);

            // 開放状態
            _unreleaseGameObject.gameObject.SetActive(!viewModel.StageReleaseStatus.IsReleased);
            _releasedGameObject.SetActive(viewModel.StageReleaseStatus.IsReleased);
            _unreleaseLockIcon.SetActive(viewModel.StageReleaseStatus.Value == StageStatus.UnRelease);
            _unreleaseText.gameObject.SetActive(viewModel.StageReleaseStatus.Value ==StageStatus.UnReleaseAtOutOfTime);
            _unreleaseText.SetText(viewModel.ReleaseRequireSentence.Value);

            // ステージ挑戦状態
            _newIconObject.SetActive(viewModel.StageClearStatus == StageClearStatus.New);
            _clearIconObject.SetActive(viewModel.StageClearStatus == StageClearStatus.Clear);
            _dailyIconObject.SetActive(viewModel.StageClearStatus == StageClearStatus.Daily);


            _artworkFragmentIconImage.gameObject.SetActive(viewModel.IsShowArtworkFragmentIcon);
            _rewardIconObject.SetActive(viewModel.IsShowRewardCompleteIcon);
        }
    }
}
