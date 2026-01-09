using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    // ゲーム起動中にサーバーで詳細内容が変わっても検知出来ないので現行未使用。
    // 表示速度など必要になれば利用検討。
    public interface IGashaPrizeCacheRepository
    {
        void Set(MasterDataId masterDataId, GachaPrizeResultModel model);
        GachaPrizeResultModel Get(MasterDataId masterDataId);
        bool TryGetValue(MasterDataId masterDataId, out GachaPrizeResultModel value);
        void Clear();
    }
}
