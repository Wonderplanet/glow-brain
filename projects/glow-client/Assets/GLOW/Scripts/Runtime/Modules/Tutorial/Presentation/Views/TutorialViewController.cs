using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.InvertMaskView.Domain.ValueObject;
using GLOW.Modules.InvertMaskView.Presentation.ValueObject;
using GLOW.Modules.InvertMaskView.Presentation.ViewModel;
using GLOW.Modules.Tutorial.Presentation.Manager;
using GLOW.Modules.TutorialMessageBox.Presentation.ViewModel;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;
using GLOW.Modules.TutorialTapIcon.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WonderPlanet.SceneManagement;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public class TutorialViewController : UIViewController<TutorialView>
    {
        ITransitionFactory _transitionFactory;
        public void SetupMask(InvertMaskViewModel maskViewModel, Action onCompletedAction)
        {
            ActualView.SetupMask(maskViewModel, onCompletedAction);
        }

        public void ShowMask(InvertMaskViewModel maskViewModel, Action onCompletedAction)
        {
            ActualView.SetupMask(maskViewModel, onCompletedAction);
            ActualView.ShowMask();
        }

        public void ShowGrayOut()
        {
            ActualView.ShowGrayOut();
        }

        public void ShowMessageBox(
            TutorialMessageBoxViewModel messageBoxViewModel,
            AllowTapOnlyInvertMaskedAreaFlag allowTapOnlyInvertMaskedAreaFlag,
            Action onCompletedAction)
        {
            ActualView.SetupMessageBox(messageBoxViewModel, allowTapOnlyInvertMaskedAreaFlag, onCompletedAction);
            ActualView.ShowMessageBox();
        }

        public void SetupTapIcon(TutorialTapIconViewModel tapIconViewModel)
        {
            ActualView.SetupTapIcon(tapIconViewModel);
        }

        public void ShowTapIcon(TutorialTapIconViewModel tapIconViewModel)
        {
            ActualView.SetupTapIcon(tapIconViewModel);
            ActualView.ShowTapIcon();
        }
        
        public void HideTapIcon()
        {
            ActualView.HideTapIcon();
        }
        
        public void ShowLongTapIcon(TutorialTapIconViewModel tapIconViewModel)
        {
            ActualView.SetupLongTapIcon(tapIconViewModel);
            ActualView.ShowLongTapIcon();
        }
        
        public void HideLongTapIcon()
        {
            ActualView.HideLongTapIcon();
        }
        
        public void ShowArrowIcon(TutorialTapIconPosition position, ReverseFlag reverseFlag)
        {
            ActualView.SetupArrowIcon(position, reverseFlag);
            ActualView.ShowArrowIcon();
        }
        
        public void HideArrowIcon()
        {
            ActualView.HideArrowIcon();
        }

        public InvertMaskPosition CalculateInvertMaskPosition(Vector2 screenPoint)
        {
            var targetCanvasRect = ActualView.GetInvertMaskParentCanvasRectTransform();
            // ScreenSpaceOverlayのため第3引数はnullを入れる
            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                targetCanvasRect, screenPoint, null, out Vector2 localPoint);

            return new InvertMaskPosition(localPoint.x, localPoint.y);
        }
        
        public void HideTutorialCanvass()
        {
            ActualView.HideTutorialCanvass();
        }

        public void FadeInGrayOut(Action completedAction)
        {
            ActualView.FadeInGrayOut(completedAction);
        }

        public void FadeOutGrayOut(Action completedAction)
        {
            ActualView.FadeOutGrayOut(completedAction);
        }

        public void ShowMask()
        {
            ActualView.ShowMask();
        }

        public void ShowMessageBox()
        {
            ActualView.ShowMessageBox();
        }

        public void UpdateMessageBoxText()
        {
            ActualView.UpdateMessageBoxText();
        }

        public void ShowTapIcon()
        {
            ActualView.ShowTapIcon();
        }

        public void HideMask()
        {
            ActualView.HideMask();
        }

        public void HideMessageBox()
        {
            ActualView.HideMessageBox();
        }
        public void HideMessageBox(Action action)
        {
            ActualView.HideMessageBox(action);
        }
        
        public void ShowSkipButton(Action onTappedAction)
        {
            ActualView.ShowSkipButton(onTappedAction);
        }
        
        public void HideSkipButton()
        {
            ActualView.HideSkipButton();
        }
        
        public void ShowCircleGaugeProgress()
        {
            ActualView.ShowDownloadGaugeProgress();
        }
        
        public void SetCircleGaugeProgress(DownloadProgress progress)
        {
            ActualView.SetDownloadGaugeProgress(progress);
        }

        public void ShowCircleGaugeCompletedText()
        {
            ActualView.ShowDownloadGaugeCompletedText();
        }
        
        public void ShowTutorialDownloadScreen()
        {
            ActualView.ShowTutorialDownloadScreen();
        }
        
        public async UniTask PlayAppearTutorialDownloadTransition(CancellationToken cancellationToken)
        {
            await ActualView.PlayAppearTutorialDownloadTransition(cancellationToken);
        }

        public async UniTask PlayDisappearTutorialDownloadTransition(CancellationToken cancellationToken)
        {
            await ActualView.PlayDisappearTutorialDownloadTransition(cancellationToken);
        }

        public async UniTask PlayTutorialManga(CancellationToken cancellationToken, TutorialIntroductionMangaManager manager)
        {
            await ActualView.PlayTutorialManga(cancellationToken, manager);
        }
    }
}
