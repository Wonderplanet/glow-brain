using System.Collections.Generic;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Repositories
{
    public class GachaCacheRepository : IGachaCacheRepository
    {
        IReadOnlyList<GachaResultModel> _gachaResultModels = new List<GachaResultModel>(); // ガシャ演出・ガシャ結果用
        GachaDrawInfoModel _gachaDrawInfoModel = GachaDrawInfoModel.Empty;
        GachaDrawFromContentViewFlag _gachaDrawFromContentViewFlag;

        IReadOnlyList<GachaResultModel> IGachaCacheRepository.GetGachaResultModels()
        {
            return _gachaResultModels;
        }

        void IGachaCacheRepository.SaveGachaResultModels(IReadOnlyList<GachaResultModel> gachaDrawResultModels)
        {
            _gachaResultModels = gachaDrawResultModels;
        }

        void IGachaCacheRepository.ClearGachaResultModels()
        {
            _gachaResultModels = new List<GachaResultModel>();
        }

        GachaDrawInfoModel IGachaCacheRepository.GetGachaDrawInfoModel()
        {
            return _gachaDrawInfoModel;
        }

        void IGachaCacheRepository.SaveGachaDrawType(GachaDrawInfoModel gachaDrawInfoModel)
        {
            _gachaDrawInfoModel = gachaDrawInfoModel;
        }

        void IGachaCacheRepository.ClearGachaDrawType()
        {
            _gachaDrawInfoModel = GachaDrawInfoModel.Empty;
        }

        GachaDrawFromContentViewFlag IGachaCacheRepository.GetGachaDrawFromContentViewFlag()
        {
            return _gachaDrawFromContentViewFlag;
        }

        void IGachaCacheRepository.SetGachaDrawFromContentViewFlag(GachaDrawFromContentViewFlag gachaDrawFromContentViewFlag)
        {
            _gachaDrawFromContentViewFlag = gachaDrawFromContentViewFlag;
        }

        void IGachaCacheRepository.ClearGachaDrawFromContentViewFlag()
        {
            _gachaDrawFromContentViewFlag = GachaDrawFromContentViewFlag.False;
        }
    }
}
