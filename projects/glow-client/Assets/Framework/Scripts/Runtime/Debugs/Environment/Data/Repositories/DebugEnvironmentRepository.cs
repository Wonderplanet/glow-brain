using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Debugs.Environment.Data.Data;
using WPFramework.Debugs.Environment.Data.DataStores;
using WPFramework.Debugs.Environment.Domain.Models;
using WPFramework.Debugs.Environment.Domain.Repositories;
using WPFramework.Domain.Models;
using WPFramework.Domain.Translators;
using Zenject;

namespace WPFramework.Debugs.Environment.Data.Repositories
{
    public sealed class DebugEnvironmentRepository :
        IDebugEnvironmentSelectRepository,
        IDebugEnvironmentSpecifiedDomainRepository,
        IDebugEnvironmentTargetRepository
    {
        [Inject] IDebugEnvironmentSelectDataStore DebugEnvironmentSelectDataStore { get; }
        [Inject] IDebugEnvironmentSpecifiedDomainDataStore DebugEnvironmentSpecifiedDomainDataStore { get; }
        [Inject] IEnvironmentTranslator EnvironmentTranslator { get; }
        [Inject] IDebugEnvironmentTargetDataStore DebugEnvironmentTargetDataStore { get; }

        async UniTask IDebugEnvironmentSelectRepository.Load(CancellationToken cancellationToken)
        {
            await DebugEnvironmentSelectDataStore.Load(cancellationToken);
        }

        void IDebugEnvironmentSelectRepository.Save(EnvironmentModel model)
        {
            DebugEnvironmentSelectDataStore.Save(EnvironmentTranslator.TranslateToData(model));
        }

        EnvironmentModel IDebugEnvironmentSelectRepository.GetLast()
        {
            var environmentData = DebugEnvironmentSelectDataStore.Get();
            return environmentData == null ? null : EnvironmentTranslator.TranslateToModel(environmentData);
        }

        async UniTask IDebugEnvironmentSpecifiedDomainRepository.Load(CancellationToken cancellationToken)
        {
            await DebugEnvironmentSpecifiedDomainDataStore.Load(cancellationToken);
        }

        void IDebugEnvironmentSpecifiedDomainRepository.Save(DebugEnvironmentSpecifiedDomainModel debugEnvironmentSpecifiedDomainModel)
        {
            DebugEnvironmentSpecifiedDomainDataStore.Save(
                new DebugEnvironmentSpecifiedDomainData(
                    EnvironmentTranslator.TranslateToData(debugEnvironmentSpecifiedDomainModel.SpecifiedEnvironment)));
        }

        DebugEnvironmentSpecifiedDomainModel IDebugEnvironmentSpecifiedDomainRepository.Get()
        {
            var debugEnvironmentInputDomainData = DebugEnvironmentSpecifiedDomainDataStore.Get();
            return debugEnvironmentInputDomainData?.SpecifiedEnvironmentData == null
                ? null
                : new DebugEnvironmentSpecifiedDomainModel(
                    EnvironmentTranslator.TranslateToModel(debugEnvironmentInputDomainData.SpecifiedEnvironmentData));
        }

        void IDebugEnvironmentSpecifiedDomainRepository.Delete()
        {
            DebugEnvironmentSpecifiedDomainDataStore.Delete();
        }

        async UniTask IDebugEnvironmentTargetRepository.Load(CancellationToken cancellationToken)
        {
            await DebugEnvironmentTargetDataStore.Load(cancellationToken);
        }

        DebugEnvironmentTargetModel IDebugEnvironmentTargetRepository.Get()
        {
            var debugEnvironmentRecommendData = DebugEnvironmentTargetDataStore.Get();
            return debugEnvironmentRecommendData == null ?
                new DebugEnvironmentTargetModel(string.Empty) :
                new DebugEnvironmentTargetModel(debugEnvironmentRecommendData.Env);
        }
    }
}
