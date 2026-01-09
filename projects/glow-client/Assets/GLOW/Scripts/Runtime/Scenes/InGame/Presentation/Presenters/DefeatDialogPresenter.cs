using Cysharp.Threading.Tasks;
using GLOW.Scenes.InGame.Presentation.Views.DefeatDialog;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public class DefeatDialogPresenter : IDefeatDialogViewDelegate
    {
        [Inject] DefeatDialogViewController ViewController { get; }
        [Inject] DefeatDialogViewController.Argument Argument { get; }

        public void OnViewDidLoad()
        {
            ViewController.SetUp(Argument.ViewModel);

            // タップしないでも自動で閉じるように
            DoAsync.Invoke(ViewController.ActualView.GetCancellationTokenOnDestroy(), async token =>
            {
                await UniTask.Delay(5000, cancellationToken:token);
                OnClose();
            });
        }

        public void OnClose()
        {
            ViewController.OnClose();
            ViewController.Dismiss();
        }
    }
}
