using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.InvertMaskView.Domain.ValueObject;
using GLOW.Modules.InvertMaskView.Presentation.View;
using GLOW.Modules.InvertMaskView.Presentation.ViewModel;
using GLOW.Modules.Tutorial.Presentation.Manager;
using GLOW.Modules.TutorialDownload.presentation;
using GLOW.Modules.TutorialMessageBox.Presentation.Components;
using GLOW.Modules.TutorialMessageBox.Presentation.ViewModel;
using GLOW.Modules.TutorialTapIcon.Presentation.Components;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;
using GLOW.Modules.TutorialTapIcon.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public class TutorialView : UIView
    {
        [SerializeField] Canvas _tutorialCanvas;
        [SerializeField] InvertMaskComponent _mask;
        [SerializeField] TutorialMessageBoxComponent _messageBox;
        [SerializeField] TutorialTapIconComponent _tapIcon;
        [SerializeField] TutorialLongTapIconComponent _longTapIcon;
        [SerializeField] TutorialTapEffectComponent _tapEffect;
        [SerializeField] TutorialLongTapEffectComponent _longTapEffect;
        [SerializeField] Button _skipButton;
        [SerializeField] DownloadGaugeComponent _progressGauge;
        [SerializeField] UIObject _downloadScreenObject;
        [SerializeField] TutorialTransitionComponent _transitionComponent;
        [SerializeField] TutorialTransitionComponent _inGameTransitionComponent;
        [SerializeField] UIObject _arrowIconObject;

        TutorialIntroductionMangaComponent _tutorialIntroductionMangaComponent;
        
        protected override void Awake()
        {
            base.Awake();
            _mask.Hidden = true;
            _messageBox.Hidden = true;
            _tapIcon.Hidden = true;
            _longTapIcon.Hidden = true;
            _tapEffect.Hidden = true;
            _longTapEffect.Hidden = true;
            _transitionComponent.Hidden = true;
            _arrowIconObject.Hidden = true;
            _inGameTransitionComponent.Hidden = true;
        }

        public void HideTutorialCanvass()
        {
            _tutorialCanvas.gameObject.SetActive(false);
        }

        public void FadeInGrayOut(Action completedAction)
        {
            _mask.FadeInGrayOut(completedAction);
        }

        public void FadeOutGrayOut(Action completedAction)
        {
            _mask.FadeOutGrayOut(completedAction);
        }

        public void SetupMask(InvertMaskViewModel maskViewModel, Action onCompletedAction)
        {
            _mask.Setup(maskViewModel);
            _mask.SetTappedAction(() =>
            {
                onCompletedAction?.Invoke();
            });
        }

        public void SetupMessageBox(TutorialMessageBoxViewModel messageBoxViewModel, AllowTapOnlyInvertMaskedAreaFlag allowTapOnlyInvertMaskedAreaFlag, Action onCompletedAction)
        {
            _messageBox.Setup(messageBoxViewModel, allowTapOnlyInvertMaskedAreaFlag, onCompletedAction);
        }

        public void SetupTapIcon(TutorialTapIconViewModel tapIconViewModel)
        {
            _tapIcon.Setup(tapIconViewModel);
            _tapEffect.Setup(tapIconViewModel);
        }
        
        public void SetupLongTapIcon(TutorialTapIconViewModel tapIconViewModel)
        {
            _longTapIcon.Setup(tapIconViewModel);
            _longTapEffect.Setup(tapIconViewModel);
        }
        
        public void SetupArrowIcon(TutorialTapIconPosition position, ReverseFlag reverseFlag)
        {
            _arrowIconObject.RectTransform.anchoredPosition = new Vector2(position.X, position.Y);
            
            // 反転させる場合はYのスケールを-1にする
            var localScaleY = reverseFlag ? -1 : 1;
            _arrowIconObject.RectTransform.localScale = new Vector3(1, localScaleY, 1);
        }
        
        public void ShowArrowIcon()
        {
            _arrowIconObject.Hidden = false;
        }
        
        public void HideArrowIcon()
        {
            _arrowIconObject.Hidden = true;
        }

        public void ShowMask()
        {
            _mask.ShowInvertMask();
        }

        public void ShowGrayOut()
        {
            _mask.ShowGrayOut();
        }

        public void ShowMessageBox()
        {
            _messageBox.Show();
        }

        public void UpdateMessageBoxText()
        {
            _messageBox.UpdateMessageBoxText();
        }

        public void ShowTapIcon()
        {
            _tapIcon.Show();
            _tapEffect.Show();
        }
        
        public void ShowLongTapIcon()
        {
            _longTapIcon.Show();
            _longTapEffect.Show();
        }

        public void HideMask()
        {
            _mask.HideInvertMask();
        }

        public void HideMessageBox()
        {
            _messageBox.Hide();
        }
        public void HideMessageBox(Action action)
        {
            _messageBox.Hide(action);
        }

        public void HideTapIcon()
        {
            _tapIcon.Hide();
            _tapEffect.Hide();
        }
        
        public void HideLongTapIcon()
        {
            _longTapIcon.Hide();
            _longTapEffect.Hide();
        }

        public RectTransform GetInvertMaskParentCanvasRectTransform()
        {
            return _mask.GetParentCanvasRectTransform();
        }
        
        public void ShowSkipButton(Action onTappedAction)
        {
            if(onTappedAction == null) return;
            
            _skipButton.onClick.RemoveAllListeners();
            _skipButton.onClick.AddListener(() =>
            {
                onTappedAction?.Invoke();
            });
            _skipButton.gameObject.SetActive(true);
        }
        
        public void HideSkipButton()
        {
            _skipButton.gameObject.SetActive(false);
        }

        public void ShowDownloadGaugeProgress()
        {
            _progressGauge.gameObject.SetActive(true);
        }

        public void SetDownloadGaugeProgress(DownloadProgress progress)
        {
            _progressGauge.SetGauge(progress);
        }
        
        public void ShowDownloadGaugeCompletedText()
        {
            _progressGauge.ShowCompletedDownloadText();
        }
        
        public void ShowTutorialDownloadScreen()
        {
            _downloadScreenObject.Hidden = false;
        }
        
        public async UniTask PlayAppearTutorialDownloadTransition(CancellationToken cancellationToken)
        {
            _transitionComponent.gameObject.SetActive(true);
            await _transitionComponent.PlayAppear(cancellationToken);
        }

        public async UniTask PlayDisappearTutorialDownloadTransition(CancellationToken cancellationToken)
        {
            await _transitionComponent.PlayDisappear(cancellationToken);
            _transitionComponent.gameObject.SetActive(false);
        }

        async UniTask PlayAppearTutorialInGameTransition(CancellationToken cancellationToken)
        {
            _inGameTransitionComponent.gameObject.SetActive(true);
            await _inGameTransitionComponent.PlayAppear(cancellationToken);
        }

        async UniTask PlayDisappearTutorialInGameTransition(CancellationToken cancellationToken)
        {
            await _inGameTransitionComponent.PlayDisappear(cancellationToken);
            _inGameTransitionComponent.gameObject.SetActive(false);
        }
        
        public async UniTask PlayTutorialManga(CancellationToken token, TutorialIntroductionMangaManager manager)
        {
            // ロードは事前にしておく
            var manga = manager.Instantiate(_tutorialCanvas.transform);
            _tutorialIntroductionMangaComponent = manga.GetComponent<TutorialIntroductionMangaComponent>();
            
            _tutorialIntroductionMangaComponent.Hidden = false;
            await _tutorialIntroductionMangaComponent.PlayTutorialManga(token);
            
            await PlayAppearTutorialInGameTransition(token);
            _tutorialIntroductionMangaComponent.Hidden = true;
            Destroy(manga);
            manager.Unload();
            await PlayDisappearTutorialInGameTransition(token);
        }
    }
}
