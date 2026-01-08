using GLOW.Scenes.InGame.Data.Data;
using GLOW.Scenes.InGame.Data.DataStores;
using UnityEngine;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.InGame.Application.Installers
{
    [CreateAssetMenu(fileName = "InGameSettingInstaller", menuName = "Installers/GLOW/InGameSettingInstaller")]
    public class InGameSettingInstaller : ScriptableObjectInstaller<InGameSettingInstaller>
    {
        [SerializeField] InGameSettingData _inGameSettingData;

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(InGameSettingInstaller), nameof(InstallBindings));

            var inGameSettingDataStore = new InGameSettingDataStore(_inGameSettingData);
            Container.Bind<IInGameSettingDataStore>().FromInstance(inGameSettingDataStore);
        }
    }
}
