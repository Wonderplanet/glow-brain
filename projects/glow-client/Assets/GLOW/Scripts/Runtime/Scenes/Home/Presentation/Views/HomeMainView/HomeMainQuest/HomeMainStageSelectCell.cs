using DG.Tweening;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.Modules;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class HomeMainStageSelectCell : GlowCustomInfiniteCarouselCell
    {
        [Header("Cell本体")]
        [SerializeField] Button _rootButton;
        [Header("未開放")]
        [SerializeField] GameObject _unReleaseGameObject;
        [SerializeField] Button _unReleaseButton;
        [Header("開放済み")]
        [SerializeField] GameObject _releasedGameObject;
        [SerializeField] UIText _releasedStageNumber;
        [SerializeField] Button _stageInfoButton;

        [Header("開放済み/ステージアイコン")]
        [SerializeField] Image _stageIconImage;
        [SerializeField] CanvasGroup _stageIconCanvasGroup;
        [Header("開放済み/クリア状況")]
        [SerializeField] GameObject _newIconObject;
        [SerializeField] GameObject _clearIconObject;
        [Header("開放済み/原画のかけらアイコン")]
        [SerializeField] Image _artworkFragmentIconImage;
        [Header("解放済み/報酬宝箱アイコン")]
        [SerializeField] GameObject _rewardIconObject;

        string _imageAssetPath;

        // チュートリアル表示用
        StageNumber _stageNumber = Core.Domain.ValueObjects.Stage.StageNumber.Empty;

        public int StageNumber
        {
            set
            {
                _stageNumber = new StageNumber(value);
                _releasedStageNumber.SetText(value.ToString());
            }

            get => _stageNumber.Value;
        }

        public GameObject ReleasedGameObject => _releasedGameObject;

        public string StageImageAssetPath
        {
            set
            {
                //毎度ロード走ると画面遷移事にちらつくので、同じときは何もしない
                if (_imageAssetPath == value)
                {
                    _stageIconCanvasGroup.alpha = 0f;
                    _stageIconCanvasGroup
                        .DOFade(1f, 0.2f)
                        .SetDelay(0.4f)
                        .Play();
                    return;
                }
                _imageAssetPath = value;

                SpriteLoaderUtil.Clear(_stageIconImage);
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                    _stageIconImage,
                    value,
                    () =>
                    {
                        if (!_stageIconCanvasGroup) return;

                        _stageIconCanvasGroup.alpha = 0f;
                        _stageIconCanvasGroup.DOFade(1f, 0.2f).SetDelay(0.1f).Play();
                    });
            }
        }

        protected override void Awake()
        {
            base.Awake();
            AddButton(_stageInfoButton, "info");
            AddButton(_unReleaseButton, "unRelease");
        }

        public void IsReleased(bool isReleased)
        {
            _unReleaseGameObject.gameObject.SetActive(!isReleased);
            _releasedGameObject.SetActive(isReleased);
        }


        public void SetStatus(StageClearStatus status)
        {
            _newIconObject.SetActive(status == StageClearStatus.New);
            _clearIconObject.SetActive(status == StageClearStatus.Clear);
        }

        public void SetArtworkFragmentStatus(bool isShow)
        {
            _artworkFragmentIconImage.gameObject.SetActive(isShow);
        }

        public void SetRewardIconStatus(bool isShow)
        {
            _rewardIconObject.SetActive(isShow);
        }

        public void OnUpdateButtonStatus(int centerIndex)
        {
            _rootButton.enabled = Index != centerIndex;

            //ここではinfoButton下のInvisibleGraphicがcellTap判定の邪魔をするのでgameObject.SetActiveする
            _stageInfoButton.gameObject.SetActive(Index == centerIndex);
            _unReleaseButton.gameObject.SetActive(Index == centerIndex);
        }
    }
}
