using GLOW.Scenes.DataRepair.Domain;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.DataRepair.Presentation
{
    /// <summary>
    /// 122_メニュー（タイトル画面）
    /// 　122-2_データ修復
    /// 　　122-2-1_データ修復
    /// </summary>
    public class DataRepairPresenter : IDataRepairViewDelegate
    {
        [Inject] CacheDeleteUseCase UseCase { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        void IDataRepairViewDelegate.OnRepair()
        {
            UseCase.DeleteForDataRepair();
            ApplicationRebootor.Reboot();
        }
    }
}
