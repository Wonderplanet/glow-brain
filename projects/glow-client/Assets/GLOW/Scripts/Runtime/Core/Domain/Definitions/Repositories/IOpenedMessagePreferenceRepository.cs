using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOpenedMessagePreferenceRepository
    {
        List<MasterDataId> OpenedMessageIds { get; }
        void SetOpenedMessageIds(List<MasterDataId> messageIds);
        void ClearOpenedMessageIds();
    }
}