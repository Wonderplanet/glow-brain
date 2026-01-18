using WPFramework.Domain.Models;
using WPFramework.Modules.Environment;

namespace WPFramework.Domain.Translators
{
    public class EnvironmentTranslator : IEnvironmentTranslator
    {
        EnvironmentData IEnvironmentTranslator.TranslateToData(EnvironmentModel environmentModel)
        {
            return new EnvironmentData(
                Env: environmentModel.Env,
                Name: environmentModel.Name,
                Description: environmentModel.Description,
                Api: environmentModel.Api,
                AssetCdn: environmentModel.AssetCdn,
                MasterCdn: environmentModel.MasterCdn,
                WebCdn: environmentModel.WebCdn,
                AnnouncementCdn: environmentModel.AnnouncementCdn,
                BannerCdn: environmentModel.BannerCdn,
                AgreementCdn: environmentModel.AgreementCdn
            );
        }

        EnvironmentModel IEnvironmentTranslator.TranslateToModel(EnvironmentData environmentData)
        {
            return new EnvironmentModel(
                Env: environmentData.Env,
                Name: environmentData.Name,
                Description: environmentData.Description,
                Api: environmentData.Api,
                AssetCdn: environmentData.AssetCdn,
                MasterCdn: environmentData.MasterCdn,
                WebCdn: environmentData.WebCdn,
                AnnouncementCdn: environmentData.AnnouncementCdn,
                BannerCdn: environmentData.BannerCdn,
                AgreementCdn: environmentData.AgreementCdn);
        }
    }
}
