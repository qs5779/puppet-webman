require 'spec_helper'

describe 'webman', :type => 'class' do
  context "On a RedHat OS with no overrides" do
    let :facts do
      {
        :osfamily => 'RedHat'
      }
    end
    it { is_expected.to contain_file('/etc/httpd/conf.d/66-webman.conf') }
    it { is_expected.to contain_file('/var/www/webman/class.manpagelookup.php') }
    it { is_expected.to contain_file('/var/www/webman/index.php') }
    it { is_expected.to contain_file('/var/www/webman/index.template') }
  end

  context "On a Debian OS with no overrides" do
    let :facts do
      {
        :osfamily => 'Debian'
      }
    end
    it { is_expected.to contain_file('/etc/apache2/conf-available/66-webman.conf') }
    it { is_expected.to contain_file('/var/www/webman/class.manpagelookup.php') }
    it { is_expected.to contain_file('/var/www/webman/index.php') }
    it { is_expected.to contain_file('/var/www/webman/index.template') }
  end

end
