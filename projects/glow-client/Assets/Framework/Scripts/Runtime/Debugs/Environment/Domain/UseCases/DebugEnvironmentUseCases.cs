using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Debugs.Environment.Domain.Modules;
using WPFramework.Debugs.Environment.Domain.Repositories;
using WPFramework.Modules.LocalStorage;
using Zenject;

namespace WPFramework.Debugs.Environment.Domain.UseCases
{
    public sealed class DebugEnvironmentUseCases
    {
        [Inject] IDebugEnvironmentSelectRepository DebugEnvironmentSelectRepository { get; }
        [Inject] IDebugEnvironmentSpecifiedDomainRepository DebugEnvironmentSpecifiedDomainRepository { get; }
        [Inject] IDebugEnvironmentService EnvironmentService { get; }
        [Inject] IDebugEnvironmentTargetRepository DebugEnvironmentTargetRepository { get; }

        public async UniTask LoadEnvironment(CancellationToken cancellationToken)
        {
            // NOTE: デバッグ時の情報を読み込む
            await UniTask.WhenAll(
                DebugEnvironmentSelectRepository.Load(cancellationToken),
                DebugEnvironmentSpecifiedDomainRepository.Load(cancellationToken),
                DebugEnvironmentTargetRepository.Load(cancellationToken));

            // NOTE: デバッグ時の情報を保存する
            await EnvironmentService.FetchEnvironment(cancellationToken);
        }

        public bool SelectEnvironment(string environmentName)
        {
            if (!EnvironmentService.ChangeConnectionEnvironment(environmentName))
            {
                return false;
            }

            var model = EnvironmentService.FindConnectionEnvironment();
            DebugEnvironmentSelectRepository.Save(model);

            return true;
        }

        public DebugEnvironmentInfoUseCaseModel FetchEnvironmentInfo()
        {
            var environmentListModel = EnvironmentService.FetchEnvironmentList();
            var lastEnvironment = DebugEnvironmentSelectRepository.GetLast();
            var recommendedEnvironment = DebugEnvironmentTargetRepository.Get();
            return new DebugEnvironmentInfoUseCaseModel(
                environmentListModel.Environments,
                lastEnvironment,
                recommendedEnvironment);
        }

        public void DeleteLocalData()
        {
            LocalStorageDeleter.DeleteAll();
        }

        public void DeletePlayerPrefs()
        {
            LocalStorageDeleter.DeletePlayerPrefs();
        }
    }
}
