using System.Collections.Generic;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Extensions;

namespace GLOW.Core.Domain.Extensions
{
    public static class BadgeModelPropertyExtension
    {
        public static IReadOnlyList<MissionEventRewardCountModel> Update(
            this IReadOnlyList<MissionEventRewardCountModel> models,
            MissionEventRewardCountModel updatedModel)
        {
            if (updatedModel.IsEmpty()) return models;

            return models.ReplaceOrAdd(model => model.MstEventId == updatedModel.MstEventId, updatedModel);
        }
        
        public static IReadOnlyList<MissionEventRewardCountModel> Update(
            this IReadOnlyList<MissionEventRewardCountModel> models,
            IReadOnlyList<MissionEventRewardCountModel> updatedModels)
        {
            var filteredUpdatedModels = updatedModels.Where(model => !model.IsEmpty()).ToList();

            return models
                .Where(model => filteredUpdatedModels.All(updatedModel => updatedModel.MstEventId != model.MstEventId))
                .Concat(filteredUpdatedModels)
                .ToList();
        }
    }
}