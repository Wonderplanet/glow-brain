using WonderPlanet.CultureSupporter.Time;
using WPFramework.Modules.TimeCalibration;
using WPFramework.Modules.TimeControls;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class TimeInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: 時間の操作をインストール
            Container.Bind<TimeManipulator>().AsCached();

            // NOTE: 時間関連のインストール
            Container.BindInterfacesTo<AwsNtpContext>().AsCached();
            Container.BindInterfacesTo<NtpCalibrator>().AsCached();
            // NOTE: 時刻コントロールをインストール
            Container.BindInterfacesTo<UtcCalibrationDateTimeOffsetSource>().AsCached();
            Container.BindInterfacesTo<UtcCalibrationDateTimeSource>().AsCached();
        }
    }
}
