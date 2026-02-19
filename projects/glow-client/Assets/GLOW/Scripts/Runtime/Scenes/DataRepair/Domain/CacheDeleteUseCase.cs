using System;
using GLOW.Core.Domain.Repositories;
using WPFramework.Constants.MasterData;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.DataRepair.Domain
{
    public class CacheDeleteUseCase
    {
        [Inject] IPreferenceRepository PreferenceRepositoryDataDeleter { get; }
        [Inject] IMasterDataManagement MasterDataManagement { get; }
        [Inject] IGlowBannerManager GlowBannerManager { get; }

        public void DeleteForDataRepair()
        {
            DeleteMstDatas();
            DeleteBannerCache();
            DeletePreferenceRepository();
            // 中断復帰データ破棄は、現行では画面到達前に必ず破棄・復帰判断するので、実装していない(仕様上は必要とされている)
        }

        void DeleteMstDatas()
        {
            // NOTE: マスターデータ関連の削除
            foreach (MasterType masterType in Enum.GetValues(typeof(MasterType)))
            {
                MasterDataManagement.DeleteAll(masterType);
            }
        }

        void DeleteBannerCache()
        {
            // NOTE: バナー関連の削除
            GlowBannerManager.ClearAllHttpCache();
        }

        void DeletePreferenceRepository()
        {
            // NOTE: ユーザーデータ関連の削除
            PreferenceRepositoryDataDeleter.DeleteAll();
        }
    }
}
