using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstAbilityDescriptionDataRepository
    {
        IReadOnlyList<MstAbilityDescriptionModel> GetAbilityDescriptionModels();
    }
}
