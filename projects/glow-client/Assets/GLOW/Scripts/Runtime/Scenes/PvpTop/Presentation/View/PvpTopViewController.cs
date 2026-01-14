using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.PvpTop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PvpTop.Presentation
{
    /// <summary>
    /// 決闘
    ///   決闘Top
    /// </summary>
    public class PvpTopViewController : UIViewController<PvpTopView>, IEscapeResponder
    {
        [Inject] IPvpTopViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ITutorialBackKeyViewDelegate TutorialBackKeyViewDelegate { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        bool _initialized;
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Register(this);

            ActualView.InitializeView();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            if (!_initialized) {
                // ChildScalerが扱うCanvapGroupの関係でDidLoadだとCanvasGroupが0のままになってしまう。
                // のでWillAppearで初期化する
                ViewDelegate.OnViewDidLoad();
                _initialized = true;
            }

            ViewDelegate.OnViewWillAppear();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnLoad();

            EscapeResponderRegistry.Unregister(this);
        }

        public void SetUpView(PvpTopViewModel viewModel)
        {
            ActualView.SetUpView(viewModel);
        }

        public void SetUpOpponentComponents(IReadOnlyList<PvpTopOpponentViewModel> opponentViewModels)
        {
            ActualView.SetUpOpponentComponents(opponentViewModels);
        }

        public void SetOpponentRefreshCoolTime(PvpOpponentRefreshCoolTime remainingTime)
        {
            ActualView.SetOpponentRefreshCoolTime(remainingTime);
        }
        
        public void SetOpponentRefreshButtonGrayOut()
        {
            ActualView.SetOpponentRefreshButtonGrayOut();
        }

        public void SelectOpponent(PvpOpponentNumber number, PvpChallengeStatus status)
        {
            ActualView.SelectOpponent(number, status);
        }

        public void UpdatePartyName(PartyName partyName)
        {
            ActualView.UpdatePartyName(partyName);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            if (TutorialBackKeyViewDelegate.IsPlayingTutorial())
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }

            if (ViewDelegate.IsStartBattle())
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }

            SystemSoundEffectProvider.PlaySeEscape();
            ViewDelegate.OnBackButtonTapped();
            return true;
        }

        [UIAction]
        void OnHelpButtonTapped() => ViewDelegate.OnHelpButtonTapped();

        [UIAction]
        void OnStageDetailButtonTapped() => ViewDelegate.OnStageDetailButtonTapped();

        [UIAction]
        void OnRankingButtonTapped() => ViewDelegate.OnRankingButtonTapped();

        [UIAction]
        void OnRewardListButtonTapped() => ViewDelegate.OnRewardListButtonTapped();

        [UIAction]
        void OnBattleStartTapped()
        {
            ViewDelegate.OnBattleStartTapped();
        }

        [UIAction]
        void OnPartyEditTapped() => ViewDelegate.OnPartyEditTapped();

        [UIAction]
        void OnBackButtonTapped() => ViewDelegate.OnBackButtonTapped();

        #region 挑戦相手
        [UIAction]
        void OnOpponentRefreshButtonTapped() => ViewDelegate.OnOpponentRefreshButtonTapped();

        [UIAction]
        void OnSelectOpponent1() => ViewDelegate.OnOpponentTapped(new PvpOpponentNumber(1));

        [UIAction]
        void OnSelectOpponent2() => ViewDelegate.OnOpponentTapped(new PvpOpponentNumber(2));

        [UIAction]
        void OnSelectOpponent3() => ViewDelegate.OnOpponentTapped(new PvpOpponentNumber(3));

        [UIAction]
        void OnDetailOpponent1() => ViewDelegate.OnOpponentInfoButtonTapped(0);

        [UIAction]
        void OnDetailOpponent2() => ViewDelegate.OnOpponentInfoButtonTapped(1);

        [UIAction]
        void OnDetailOpponent3() => ViewDelegate.OnOpponentInfoButtonTapped(2);
        #endregion
    }
}
