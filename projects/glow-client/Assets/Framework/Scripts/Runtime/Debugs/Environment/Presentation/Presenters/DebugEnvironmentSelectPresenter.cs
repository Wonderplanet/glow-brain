using System.Collections.Generic;
using System.Linq;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Constants.Zenject;
using WPFramework.Debugs.Environment.Domain.UseCases;
using WPFramework.Debugs.Environment.Presentation.ViewModels;
using WPFramework.Debugs.Environment.Presentation.Views;
using WPFramework.Domain.Models;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace WPFramework.Debugs.Environment.Presentation.Presenters
{
    public sealed class DebugEnvironmentSelectPresenter : IDebugEnvironmentSelectViewDelegate
    {
        [Inject] DebugEnvironmentUseCases UseCases { get; }
        [Inject] DebugEnvironmentSelectViewController SelectViewController { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        [Inject(Id = FrameworkInjectId.Canvas.System)]
        UICanvas Canvas { get; }

        [Inject] Context Context { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IAlertUtil AlertUtil { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        EnvironmentModel _lastEnvironment;

        void IDebugEnvironmentSelectViewDelegate.OnViewDidLoad()
        {
            DoAsync.Invoke(SelectViewController.ActualView, ScreenInteractionControl, async cancellationToken =>
            {
                await UseCases.LoadEnvironment(cancellationToken);

                var environmentInfo = UseCases.FetchEnvironmentInfo();
                var environmentViewModelList = new List<DebugEnvironmentViewModel>();
                _lastEnvironment = environmentInfo.LastEnvironment;

                ApplicationLog.Log(nameof(DebugEnvironmentSelectPresenter),
                    $"EnvironmentModelList.Environments.Length: {environmentInfo.Environments.Length}");
                foreach (var environment in environmentInfo.Environments)
                {
                    var isLast = _lastEnvironment != null && environment.Env == _lastEnvironment.Env;
                    var isTarget = environment.Env == environmentInfo.TargetEnvironment.Env;
                    var viewModel = new DebugEnvironmentViewModel(
                        Env: environment.Env,
                        Name: environment.Name,
                        Api: environment.Api,
                        Description: environment.Description,
                        IsLast: isLast,
                        IsTarget: isTarget);
                    environmentViewModelList.Add(viewModel);
                }

                var environmentViewModels = environmentViewModelList.OrderBy(x => !x.IsLast).ToArray();
                var environmentViewListModel = new DebugEnvironmentViewListModel(environmentViewModels);
                SelectViewController.SetEnvironmentViewModel(environmentViewListModel);
            });
        }

        void IDebugEnvironmentSelectViewDelegate.OnSelectedEnvironment(DebugEnvironmentViewModel viewModel)
        {
            if (viewModel.Env != _lastEnvironment?.Env)
            {
                AlertUtil.ShowAlertConfirm(
                    "環境変更",
                    "前回と異なる環境を選択しました\n端末に保存された一時データを削除します\nよろしいですか？\n" +
                    "(プレイヤーデータは削除されません)",
                    () =>
                    {
                        UseCases.DeletePlayerPrefs();
                        SelectedEnvironment(viewModel);
                    },
                    () => {});
                return;
            }

            SelectedEnvironment(viewModel);
        }

        void IDebugEnvironmentSelectViewDelegate.OnDeleteLocalData()
        {
            AlertUtil.ShowAlertConfirm(
                "データを削除",
                "全てのデータの削除を行います。\nよろしいですか？",
                () =>
                {
                    UseCases.DeleteLocalData();
                    SelectViewController.Dismiss(completion: () =>
                    {
                        ApplicationRebootor.Reboot();
                        SelectViewController.OnDismissalWithDataDeletion(true);
                    });
                });
        }

        void IDebugEnvironmentSelectViewDelegate.OnSpecifiedDomainSetting()
        {
            var argument = new DebugEnvironmentSpecifiedDomainViewController.Arguments(
                () => ApplicationRebootor.Reboot(),
                () => ApplicationRebootor.Reboot(),
                onCancel: () => { });
            var controller =
                ViewFactory.Create<DebugEnvironmentSpecifiedDomainViewController, DebugEnvironmentSpecifiedDomainViewController.Arguments>(argument);
            Canvas.RootViewController.PresentModally(controller);
        }
        
        void SelectedEnvironment(DebugEnvironmentViewModel viewModel)
        {
            if (!UseCases.SelectEnvironment(viewModel.Env))
            {
                ApplicationLog.LogError(nameof(DebugEnvironmentSelectPresenter),
                    $"Failed to set environment: ({viewModel.Env}:{viewModel.Name})");
            }
            else
            {
                ApplicationLog.Log(nameof(DebugEnvironmentSelectPresenter),
                    $"Set environment: ({viewModel.Env}:{viewModel.Name})");
            }

            SelectViewController.Dismiss(completion: () =>
            {
                SelectViewController.OnDismissalWithDataDeletion(false);
            });
        }
    }
}
