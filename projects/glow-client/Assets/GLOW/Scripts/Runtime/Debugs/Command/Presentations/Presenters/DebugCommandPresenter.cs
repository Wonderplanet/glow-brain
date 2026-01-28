#if GLOW_DEBUG
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations.ViewModels;
using GLOW.Debugs.Command.Presentations.Views;

using System;
using System.Collections.Generic;
using System.Linq;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Debugs.Command.Presentations.Presenters
{
    public sealed class DebugCommandPresenter : IDebugCommandViewDelegate, IDebugCommandPresenter
    {
        [Inject] DebugCommandUseCases UseCases { get; }

        DebugCommandViewController ViewController { get; set; }

        List<Action<IDebugCommandPresenter>> _nestedMenuStack = new();

        public Action<IDebugCommandPresenter> DidLoad { get; set; }
        public Action DidUnload { get; set; }
        public Action<IDebugCommandPresenter> CreateRootMenu { get; set; }

        void IDebugCommandViewDelegate.OnViewDidLoad(DebugCommandViewController viewController)
        {
            // NOTE: 生成方法が特殊なためViewDelegate経由でViewControllerの参照を解決する
            ViewController = viewController;

            // NOTE: 現在時刻を見続け処理を走らせる
            UpdateCurrentTime();
            MonitorCurrentTime();

            // NOTE: 画面更新
            var model = UseCases.GetDebugUseCaseModel();
            var viewModel = new DebugCommandViewModel(
                new DebugCommandTimeViewModel(model.CurrentTime),
                model.EnvName);
            ViewController.SetViewModel(viewModel);

            DidLoad?.Invoke(this);

            _nestedMenuStack.Add(CreateRootMenu);
            CreateRootMenu?.Invoke(this);
        }

        void IDebugCommandViewDelegate.ViewDidUnload()
        {
            DidUnload?.Invoke();
        }

        public void AddButton(string text, Action onTapped)
        {
            ViewController.AddButton(text, onTapped);
        }

        public void AddToggleButton(string text, bool isOn, Action<bool> onTapped)
        {
            ViewController.AddToggleButton(text, isOn, onTapped);

        }

        public void AddTextBox(string text, string defaultInputFieldText, Action<string> onEndEdit)
        {
            ViewController.AddTextBox(text, defaultInputFieldText, onEndEdit);
        }

        public void AddInputToggle(string text, string defaultInputFieldText, bool isOn, Action<bool> onTapped, Action<string> onEndEdit)
        {
            ViewController.AddInputToggle(text, defaultInputFieldText, isOn, onTapped, onEndEdit);
        }

        public void AddDropdownInputToggle(string text, List<(bool, string)> defaultValue, Action<List<(bool, string)>> onValueChanged)
        {
            ViewController.AddDropdownInputToggle(text, defaultValue, onValueChanged);
        }

        public void AddStateButton(string text, string[] states, string defaultValue, Action<string> onChanged)
        {
            ViewController.AddStateButton(text, states, defaultValue, onChanged);
        }

        public void AddNestedMenuButton(string text, Action<IDebugCommandPresenter> createNestedMenu)
        {
            ViewController.AddButton(text, () =>
            {
                ViewController.ClearMenu();

                _nestedMenuStack.Add(createNestedMenu);

                ShowCurrentMenu();
            });
        }

        public void CloseMenu()
        {
            ViewController.ViewClose();
        }

        public void UpdateMenu(IDebugCommandPresenter debugCommandPresenter, Action<IDebugCommandPresenter> createMenuAction)
        {
            ViewController.ClearMenu();
            createMenuAction(debugCommandPresenter);
            if (_nestedMenuStack.Count <= 1) return;

            ViewController.AddButton("戻る", NavigateBack);
        }

        void MonitorCurrentTime()
        {
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                // NOTE: 毎ループ確認する
                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    // NOTE: CancellationTokenがキャンセルされたら処理を終了する
                    if (cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    await UniTask.Delay(TimeSpan.FromSeconds(1.0), cancellationToken: cancellationToken);

                    UpdateCurrentTime();
                }
            });
        }

        void ShowCurrentMenu()
        {
            _nestedMenuStack.Last()?.Invoke(this);

            if (_nestedMenuStack.Count > 1)
            {
                ViewController.AddButton("戻る", NavigateBack);
            }
        }

        void NavigateBack()
        {
            ViewController.ClearMenu();
            _nestedMenuStack.RemoveAt(_nestedMenuStack.Count - 1);

            ShowCurrentMenu();
        }

        void UpdateCurrentTime()
        {
            var model = UseCases.GetDebugUseCaseModel();
            var timeViewModel = new DebugCommandTimeViewModel(model.CurrentTime);
            ViewController.SetTimeViewModel(timeViewModel);
        }
    }
}
#endif //GLOW_DEBUG
