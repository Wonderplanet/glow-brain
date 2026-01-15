using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.UseCases;
using GLOW.Scenes.BattleResult.Domain.UseCases;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Presentation.Views;
using GLOW.Scenes.InGame.Presentation.Views.InGameMenu;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public class InGameMenuPresenter : IInGameMenuViewDelegate
    {
        [Inject] AbortUseCase AbortUseCase { get; }
        [Inject] PvpGiveUpUseCase PvpGiveUpUseCase { get; }
        [Inject] InGameMenuViewController ViewController { get; }

        [Inject] ShowInGameMenuUseCase ShowInGameMenuUseCase { get; }

        [Inject] SwitchBgmGameOptionUseCase SwitchBgmGameOptionUseCase { get; }
        [Inject] SwitchSeGameOptionUseCase SwitchSeGameOptionUseCase { get; }
        [Inject] SetSpecialAttackCutInPlayTypeGameOptionUseCase SetSpecialAttackCutInPlayTypeGameOptionUseCase { get; }
        [Inject] SwitchTwoRowDeckGameOptionUseCase SwitchTwoRowDeckGameOptionUseCase { get; }
        [Inject] SwitchDamageDisplayGameOptionUseCase SwitchDamageDisplayGameOptionUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] CheckContentOpenUseCase CheckContentOpenUseCase { get; }
        [Inject] IPeriodOutsideExceptionWireframe PeriodOutsideExceptionWireframe { get; }

        CancellationToken InGameMenuCancellationToken => ViewController.View.GetCancellationTokenOnDestroy();
        
        InGameTypePvpFlag _inGameTypePvpFlag;

        void IInGameMenuViewDelegate.OnViewDidLoad()
        {
            var inGameMenuModel = ShowInGameMenuUseCase.GetInGameMenu();
            ViewController.Setup(inGameMenuModel);
            
            _inGameTypePvpFlag = inGameMenuModel.IsInGameTypePvp;
        }

        void IInGameMenuViewDelegate.OnViewDidUnload()
        {

        }

        void IInGameMenuViewDelegate.Abort()
        {
            DoAsync.Invoke(InGameMenuCancellationToken, ScreenInteractionControl, async ct =>
            {
                if (_inGameTypePvpFlag)
                {
                    PvpGiveUpUseCase.GiveUp();
                    ViewController.CloseView();
                }
                else
                {
                    await AbortUseCase.Abort(ct);
                    
                    var model = CheckContentOpenUseCase.CheckContentOpenStatus();
                    if (model.IsInGameStageValid == InGameStageValidFlag.False)
                    {
                        PeriodOutsideExceptionWireframe.ShowPeriodOutsideExceptionMessage(model, ViewController.TransitToHome);
                        return;
                    }
                    
                    ViewController.TransitToHome();
                }
            });
        }

        void IInGameMenuViewDelegate.OnBgmMuteToggleSwitched()
        {
            var isMute = SwitchBgmGameOptionUseCase.SwitchBgmGameOption();
            ViewController.SetBgmToggleOn(isMute);
        }

        void IInGameMenuViewDelegate.OnSeMuteToggleSwitched()
        {
            var isMute = SwitchSeGameOptionUseCase.SwitchSeGameOption();
            ViewController.SetSeToggleOn(isMute);
        }

        void IInGameMenuViewDelegate.OnSpecialAttackCutInToggleSwitched(SpecialAttackCutInPlayType specialAttackCutInPlayType)
        {
            var playType = SetSpecialAttackCutInPlayTypeGameOptionUseCase
                .SetSpecialAttackCutInPlayTypeGameOption(specialAttackCutInPlayType);

            ViewController.SetSpecialAttackCutInToggleOn(playType);
        }

        void IInGameMenuViewDelegate.OnTwoRowDeckToggleSwitched()
        {
            var isTwoRowDeck = SwitchTwoRowDeckGameOptionUseCase.SwitchTwoRowDeckGameOption();
            ViewController.SetTwoRowDeckToggleOn(isTwoRowDeck);
        }

        void IInGameMenuViewDelegate.OnDamageDisplayToggleSwitched()
        {
            var isDamageDisplay = SwitchDamageDisplayGameOptionUseCase.SwitchDamageDisplayGameOption();
            ViewController.SetDamageDisplayToggleOn(isDamageDisplay);
        }
    }
}
