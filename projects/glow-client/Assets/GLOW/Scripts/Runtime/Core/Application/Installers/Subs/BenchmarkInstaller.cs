using WPFramework.Modules.Benchmark;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    internal sealed class BenchmarkInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: 時間計測機構のコンテナをインストール
            //       このコンテナは、時間計測機構をレイヤーを跨いでアクセスしたりする際に管理するためのコンテナです。
            Container.Bind<TimeMeasurementContainer>().AsCached();
        }
    }
}
