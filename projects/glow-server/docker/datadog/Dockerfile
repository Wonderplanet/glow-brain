FROM amazonlinux:2023

RUN dnf -y update \
  && dnf install -y \
  jq \
  procps-ng \
  python3-pip \
  && dnf clean all
RUN pip install supervisor

# Install Datadog agent
RUN curl -L https://install.datadoghq.com/scripts/install_script_agent7.sh -o /tmp/install_script.sh && \
  chmod +x /tmp/install_script.sh && \
  # Set dummy API key to avoid installation prompt
  DD_INSTALL_ONLY=true DD_API_KEY=dummy bash /tmp/install_script.sh && \
  rm /tmp/install_script.sh

# Create directories for Datadog
RUN mkdir -p /var/run/datadog && \
  mkdir -p /etc/datadog-agent/conf.d/php_fpm.d && \
  mkdir -p /var/log/supervisor

# Set permissions
RUN chown -R dd-agent:dd-agent /var/run/datadog && \
  chmod 755 /var/run/datadog && \
  chown -R dd-agent:dd-agent /etc/datadog-agent/conf.d/php_fpm.d

# Copy Datadog configuration
COPY docker/datadog/datadog.yaml /etc/datadog-agent/datadog.yaml
RUN chown dd-agent:dd-agent /etc/datadog-agent/datadog.yaml && \
  chmod 640 /etc/datadog-agent/datadog.yaml

# Copy metadata fetcher script
COPY docker/datadog/metadata-fetcher.sh /metadata-fetcher.sh
RUN chmod +x /metadata-fetcher.sh

# Copy supervisor configuration
COPY docker/datadog/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/local/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]