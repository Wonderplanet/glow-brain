using WPFramework.Debugs.Environment.Domain.Models;
using WPFramework.Debugs.Environment.Domain.Repositories;
using WPFramework.Domain.Models;
using WPFramework.Domain.Translators;
using WPFramework.Modules.Environment;
using Zenject;

namespace WPFramework.Debugs.Environment.Domain.UseCases
{
    public sealed class DebugEnvironmentSpecifiedDomainUseCases
    {
        const string SpecifiedDomainEnvName = "Specified Domain";

        [Inject] IDebugEnvironmentSpecifiedDomainRepository DebugEnvironmentSpecifiedDomainRepository { get; }
        [Inject] EnvironmentCoordinator EnvironmentCoordinator { get; }
        [Inject] IEnvironmentTranslator EnvironmentTranslator { get; }

        public EnvironmentModel GetOrCreateSpecifiedEnvironment()
        {
            var newSpecifiedEnvironmentModel = DebugEnvironmentSpecifiedDomainRepository.Get();

            if (newSpecifiedEnvironmentModel != null)
            {
                return newSpecifiedEnvironmentModel.SpecifiedEnvironment;
            }

            // NOTE: データが見つからなかった場合はリストの１番目のデータをコピーして利用する
            var currentEnvironmentData = EnvironmentCoordinator.FindConnectionEnvironment();
            var newEnvironmentData =
                new EnvironmentData(
                    Env: SpecifiedDomainEnvName,
                    Name: SpecifiedDomainEnvName,
                    currentEnvironmentData.Description,
                    currentEnvironmentData.Api,
                    currentEnvironmentData.AssetCdn,
                    currentEnvironmentData.MasterCdn,
                    currentEnvironmentData.WebCdn,
                    currentEnvironmentData.AnnouncementCdn,
                    currentEnvironmentData.BannerCdn,
                    currentEnvironmentData.AgreementCdn);
            var newEnvironmentModel = EnvironmentTranslator.TranslateToModel(newEnvironmentData);
            return newEnvironmentModel;
        }

        public void SaveSpecifiedEnvironment(EnvironmentModel environmentModel)
        {
            var model = new DebugEnvironmentSpecifiedDomainModel(environmentModel);
            DebugEnvironmentSpecifiedDomainRepository.Save(model);
        }

        public void ResetSpecifiedEnvironment()
        {
            DebugEnvironmentSpecifiedDomainRepository.Delete();
        }
    }
}
