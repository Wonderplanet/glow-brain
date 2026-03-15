using System.Collections.Generic;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Repositories
{
    public class GachaCacheRepository : IGachaCacheRepository
    {
        IReadOnlyList<GachaResultModel> _gachaResultModels = new List<GachaResultModel>(); // ガシャ演出・ガシャ結果用
        IReadOnlyList<GachaResultModel> _stepRewardModels = new List<GachaResultModel>(); // ステップアップガシャのおまけ用
        GachaDrawInfoModel _gachaDrawInfoModel = GachaDrawInfoModel.Empty;

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

        IReadOnlyList<GachaResultModel> IGachaCacheRepository.GetStepRewardModels()
        {
            return _stepRewardModels;
        }

        void IGachaCacheRepository.SaveStepRewardModels(IReadOnlyList<GachaResultModel> stepRewardModels)
        {
            _stepRewardModels = stepRewardModels;
        }

        void IGachaCacheRepository.ClearStepRewardModels()
        {
            _stepRewardModels = new List<GachaResultModel>();
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
    }
}
